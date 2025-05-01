<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\GoogleUserType;
use App\Form\LoginFormType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

#[Route('/user')]
final class UserController extends AbstractController
{
    private UserRepository $userRepository;
    private EmailService $emailService;

    public function __construct(UserRepository $userRepository, EmailService $emailService){
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('FrontOffice/home.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/admin/users', name: 'app_admin_Listusers', methods: ['GET'])]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        // Get filter parameters from request
        $filters = [
            'cin' => $request->query->get('cin'),
            'email' => $request->query->get('email'),
            'age' => $request->query->get('age'),
        ];

        // Get sorting parameters
        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');

        // Validate sort direction
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? $direction : 'ASC';

        return $this->render('BackOffice/amine/users.html.twig', [
            'users' => $userRepository->findAllWithFilters($filters, $sort, $direction),
            'current_filters' => $filters,
            'current_sort' => $sort,
            'current_direction' => $direction,
        ]);
    }

//    #[Route('/admin/users',name: 'app_admin_Listusers', methods: ['GET'])]
//    public function users(UserRepository $userRepository): Response
//    {
//        return $this->render('BackOffice/amine/users.html.twig', [
//            'users' => $userRepository->findAll(),
//        ]);
//    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        FileUploader $fileUploader
    ): Response {
        $user = new User();
        $form = $this->createForm(GoogleUserType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            //verify email unicity
            $existingUser= $this->userRepository->findByEmail($user->getEmail());
            if ($existingUser) {
                $this->addFlash('error', 'This email address is already registered.');
                return $this->redirectToRoute('app_user_new');
            }

            // Hash the password
            $plainPassword = $form->get('Password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plainPassword
                );
                $user->setPassword($hashedPassword);
            }

            // Set role
            $user->setRoles($user->getRoles() ?: ['ROLE_USER']);

            //uploading profile picture
            $profilePictureFile = $form->get('profilePicture')->getData();
            if ($profilePictureFile) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($profilePictureFile->getMimeType(), $allowedMimeTypes)) {
                    // Add error message
                    $this->addFlash('error', 'Invalid file type. Only JPEG, PNG and GIF are allowed.');
                    return $this->redirectToRoute('app_user_new');
                }

                $fileName = $fileUploader->upload($profilePictureFile);
                $logger->debug('User entity before processing:', [
                    'role' => $fileName,
                ]);
                $user->setPathToPic('/uploads/profile_pictures/' . $fileName);
            }

            //verifying the information
            $logger->debug('User entity before processing:', [
                'role' => $user->getRoles()[0],
                'pic' => $user->getPathToPic(),
            ]);

            echo("the final user is , ". $user->getPassword());

            //sending welcome email
            $this->emailService->sendEmail(
                $user->getEmail(),
                'Welcome to Our Site!',
                'Emails/welcome.html.twig',
                ['user' => $user]
            );

            //saving the entity
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/complete-profile', name: 'app_user_complete_profile', methods: ['GET', 'POST'])]
    public function completeProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository,
        SessionInterface $session
    ): Response {
        /** @var User $user */
        $userId = $request->getSession()->get('pending_user_id');

        if (!$userId) {
            $this->addFlash('error', 'Session expired. Please login again.');
            return $this->redirectToRoute('app_login');
        }

        /** @var User $user */
        $user = $userRepository->find($userId);

        $form = $this->createForm(GoogleUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password
            $plainPassword = $form->get('Password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Verify CIN was changed
            if ($user->getCIN() === '00000000') {
                $this->addFlash('error', 'You must provide a valid CIN');
                return $this->redirectToRoute('app_user_complete_profile');
            }

            $entityManager->flush();
            $this->addFlash('success', 'Profile completed successfully!');
            $session->set('user_id', $user->getId());
            return $this->redirectToRoute('home');
        }

        return $this->render('user/complete_profile.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/login', name: 'app_user_login', methods: ['GET', 'POST'])]
    public function login(
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        SessionInterface $session
    ): Response {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findByEmail($data['email']);

            if (!$user) {
                $this->addFlash('error', 'Invalid credentials.');
                return $this->redirectToRoute('app_user_login');
            }

            if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
                $this->addFlash('error', 'Invalid credentials.');
                return $this->redirectToRoute('app_user_login');
            }

            // Login successful - do something with the user
            $session->set('user_id', $user->getId());

            if($user->getRole() == 'ROLE_USER'){
                $this->addFlash('success', 'Login successful!');
                return $this->redirectToRoute('app_user_index');
            }else{
                $this->addFlash('success', 'Login successful!');
                return $this->redirectToRoute('admin');
            }

        }
        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logoutuser', name: 'app_logoutuser')]
    public function logout(SessionInterface $session): RedirectResponse
    {

        // Clear the session manually
        $session->remove('user_id');  // Remove specific session data
        $session->invalidate();

        // Optionally, you can add a flash message
        $this->addFlash('success', 'You have been logged out.');

        // Redirect to the home page or any other page after logout
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('BackOffice/amine/showUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password update only if a new one was provided
            $newPassword = $form->get('Password')->getData();
            if ($newPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_Listusers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('isActive/{id}', name: 'app_user_updateStatus', methods: ['POST'])]
    public function updateStatus(Request $request, User $user, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $user=$userRepository->findById($user->getId());
        $user->isActive() ? $user->setIsActive(false): $user->setIsActive(true);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('BackOffice/amine/showUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('isVerified/{id}', name: 'app_user_updateVerified', methods: ['POST'])]
    public function updateVerified(Request $request, User $user, EntityManagerInterface $entityManager,UserRepository $userRepository): Response
    {
        $user=$userRepository->findById($user->getId());
        $user->isVerified() ? $user->setIsVerified(false): $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('BackOffice/amine/showUser.html.twig', [
            'user' => $user,
        ]);
    }

//    #[Route('/connect/google', name: 'connect_google_start')]
//    public function connect(ClientRegistry $clientRegistry): RedirectResponse
//    {
//        return $clientRegistry->getClient('google')->redirect(['email','profile','openid'], []);
//    }
//
//    #[Route('/connect/google/check', name: 'connect_google_check')]
//    public function check(): void
//    {
//        // This route is intercepted by your authenticator
//        throw new \Exception('Should not be reached directly.');
//    }
}
