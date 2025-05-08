<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\GoogleUserType;
use App\Form\LoginFormType;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\EmailService;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Psr\Log\LoggerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[Route('/home')]
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
        return $this->render('FrontOffice/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/admin/users', name: 'app_admin_Listusers', methods: ['GET'])]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $filters = [
            'cin' => $request->query->get('cin'),
            'email' => $request->query->get('email'),
            'age' => $request->query->get('age'),
        ];

        $sort = $request->query->get('sort');
        $direction = $request->query->get('direction', 'ASC');
        $direction = in_array(strtoupper($direction), ['ASC', 'DESC']) ? $direction : 'ASC';

        $users = $userRepository->findAllWithFilters($filters, $sort, $direction);

        // 👇 Example chart data (age distribution)
        $ageCounts = [];
        foreach ($users as $user) {
            $age = (new \DateTime())->diff($user->getBirthday())->y;
            $ageCounts[$age] = ($ageCounts[$age] ?? 0) + 1;
        }

        ksort($ageCounts); // Sort by age

        // Get user statistics
        $totalUsers = $userRepository->countTotalUsers();
        $activeUsers = $userRepository->countActiveUsers();
        $verifiedUsers = $userRepository->countVerifiedUsers();
        $inactiveUsers = $totalUsers - $activeUsers;
        $unverifiedUsers = $totalUsers - $verifiedUsers;

        return $this->render('BackOffice/amine/users.html.twig', [
            'users' => $users,
            'current_filters' => $filters,
            'current_sort' => $sort,
            'current_direction' => $direction,
            'chart_labels' => array_keys($ageCounts),
            'chart_data' => array_values($ageCounts),
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'verified_users' => $verifiedUsers,
            'inactive_users' => $inactiveUsers,
            'unverified_users' => $unverifiedUsers,
        ]);
    }



//    #[Route('/admin/users',name: 'app_admin_Listusers', methods: ['GET'])]
//    public function users(UserRepository $userRepository): Response
//    {
//        return $this->render('BackOffice/amine/users.html.twig', [
//            'users' => $userRepository->findAll(),
//        ]);
//    }


    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        return $this->redirectToRoute('app_user_login');
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        return $this->render('security/check_email.html.twig', [
            'pageTitle' => 'Check your email'
        ]);
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resendVerification(
        Request $request,
        UserRepository $userRepository,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer
    ): Response {
        $email = $request->getSession()->get('last_registered_email');

        if (!$email) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->findOneBy(['Email' => $email]);

        if (!$user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $signatureComponents = $verifyEmailHelper->generateSignature(
                'app_verify_email',
                $user->getId(), // 🔴 NOW HAS VALID ID
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = (new TemplatedEmail())
                ->from('no-reply@example.com')
                ->to($user->getEmail())
                ->subject('Verify Your Email')
                ->htmlTemplate('emails/verify_email.html.twig')
                ->context([
                    'signedUrl' => $signatureComponents->getSignedUrl(),
                    'user' => $user,
                ]);

            $mailer->send($email);
        } catch (\Exception $e) {
            // 🔴 HANDLE EMAIL FAILURES
            $this->addFlash('warning', 'Verification email could not be sent. Please contact support.');
        }

        $this->addFlash('success', 'Verification email resent!');
        return $this->redirectToRoute('app_check_email');
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        LoggerInterface $logger,
        FileUploader $fileUploader,
        VerifyEmailHelperInterface $verifyEmailHelper,
        MailerInterface $mailer,
        UserRepository $userRepository // 🔴 ADD THIS TO ACCESS findByEmail
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Email uniqueness check
            $existingUser = $userRepository->findOneBy(['Email' => $user->getEmail()]); // 🔴 USE findOneBy instead of findByEmail unless you have a custom method
            if ($existingUser) {
                $this->addFlash('error', 'This email address is already registered.');
                return $this->redirectToRoute('app_user_new');
            }

            // Hash password
            $plainPassword = $form->get('Password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Profile picture handling (consider moving validation to form type)
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

            // 🔴 PERSIST & FLUSH FIRST TO GET USER ID
            $entityManager->persist($user);
            $entityManager->flush();
            $request->getSession()->set('last_registered_email', $user->getEmail());

            // 🔴 MOVE EMAIL VERIFICATION LOGIC AFTER FLUSH
            try {
                $signatureComponents = $verifyEmailHelper->generateSignature(
                    'app_verify_email',
                    $user->getId(), // 🔴 NOW HAS VALID ID
                    $user->getEmail(),
                    ['id' => $user->getId()]
                );

                $email = (new TemplatedEmail())
                    ->from('no-reply@example.com')
                    ->to($user->getEmail())
                    ->subject('Verify Your Email')
                    ->htmlTemplate('emails/verify_email.html.twig')
                    ->context([
                        'signedUrl' => $signatureComponents->getSignedUrl(),
                        'user' => $user,
                    ]);

                $mailer->send($email);
            } catch (\Exception $e) {
                // 🔴 HANDLE EMAIL FAILURES
                $this->addFlash('warning', 'Verification email could not be sent. Please contact support.');
                $logger->error('Email sending failed: '.$e->getMessage());
            }

            // 🔴 REMOVE DUPLICATE REDIRECT
            return $this->redirectToRoute('app_check_email');
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
        // Debug session
        dump($request->getSession()->all());

        /** @var User $user */
        $userId = $request->getSession()->get('pending_user_id');

        if (!$userId) {
            $this->addFlash('error', 'Session expired. Please login again.');
            return $this->redirectToRoute('app_user_login');
        }

        /** @var User $user */
        $user = $userRepository->find($userId);

        // Debug user
        dump($user);

        $form = $this->createForm(GoogleUserType::class, $user);
        $form->handleRequest($request);

        // Debug form submission
        if ($request->isMethod('POST')) {
            dump('Form submitted:', $form->isSubmitted());
            dump('Form valid:', $form->isValid());
            dump('Form errors:', $form->getErrors(true));
            dump('Request data:', $request->request->all());
        }

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Handle password only if provided
                $plainPassword = $form->get('Password')->getData();
                if (!empty($plainPassword)) {
                    $plainPassword = $form->get('Password')->getData();
                    $this->addFlash('error', 'Submitted password: ' . ($plainPassword ?: '[empty]'));
                    $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                    $user->setPassword($hashedPassword);
                }

                // Verify CIN was changed
                if ($user->getCIN() === '00000000') {
                    $this->addFlash('error', 'You must provide a valid CIN');
                    return $this->redirectToRoute('app_user_complete_profile');
                }
                $user->setIsGoogleAuthenticatorEnabled(false);
                $entityManager->flush();
                $this->addFlash('success', 'Profile completed successfully!');
                $session->remove('pending_user_id');
                $session->set('user_id', $user->getId());
                return $this->redirectToRoute('app_user_index');
            } else {
                $plainPassword = $form->get('Password')->getData();
                $this->addFlash('error', 'Submitted password: ' . ($plainPassword ?: '[empty]'));

                // Form is invalid - debug why
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
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
        dump($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $userRepository->findByEmail($data['email']);
            dump($request->request->all());

            if (!$user) {
                $this->addFlash('error', 'Invalid credentials.');
                return $this->redirectToRoute('app_user_login');
            }

            if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
                $this->addFlash('error', 'Invalid credentials.');
                return $this->redirectToRoute('app_user_login');
            }

            $session->set('user_id', $user->getId());

            if ($user->isGoogleAuthenticatorEnabled()) {
                return $this->redirectToRoute('2fa_verify_code');
            }

            $this->addFlash('success', 'Login successful!');

            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('backoffice_dashboard'); // Redirect admin to /backoffice
            }
            if (in_array('ROLE_USER', $user->getRoles())) {
                return $this->redirectToRoute('app_user_index'); // Redirect user to home
            }
            // Default fallback
            return $this->redirectToRoute('app_user_index');
        }

        return $this->render('user/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/2fa/verify', name: '2fa_verify_code', methods: ['GET', 'POST'])]
    public function verifyCode(
        Request $request,
        SessionInterface $session,
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->redirectToRoute('app_user_login');
        }

        $user = $userRepository->find($userId);
        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }

        if ($request->isMethod('POST')) {
            $code = $request->request->get('code');

            if ($googleAuthenticator->checkCode($user, $code)) {
                // 2FA verified
                $session->set('2fa_verified', true);

                $this->addFlash('success', '2FA verification successful!');

                // Redirect based on role or your logic
                if (in_array('ROLE_ADMIN', $user->getRoles())) {
                    return $this->redirectToRoute('backoffice_dashboard');
                }

                return $this->redirectToRoute('app_user_index');
            } else {
                $this->addFlash('error', 'Invalid 2FA code.');
            }
        }

        return $this->render('security/verify_2fa.html.twig', [
            'email' => $user->getEmail(),
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
        return $this->redirectToRoute('app_user_login');
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('BackOffice/amine/showUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/test', name: 'page')]
    public function testi(): Response
    {
        return $this->render('FrontOffice/elements.html.twig');
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
//        $hashedPassword = $passwordHasher->hashPassword($user, 'Snprb120401!');
//        $user->setPassword($hashedPassword);
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password update only if a new one was provided
            $newPassword = $form->get('Password')->getData();
            if ($newPassword) {
                $this->addFlash('info', 'Submitted password: ' . $newPassword); // Debug only
                $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'User updated successfully');
            return $this->redirectToRoute('home', [], Response::HTTP_SEE_OTHER);
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
