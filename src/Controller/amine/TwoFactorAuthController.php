<?php

namespace App\Controller\amine;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorAuthController extends AbstractController
{
    #[Route('/enable-2fa', name: 'app_enable_2fa')]
    public function enable2fa(
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        UserRepository $userRepository
    ): Response {
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to access this page.');
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('app_login');
        }

        $secret = $googleAuthenticator->generateSecret();
        $user->setGoogleAuthenticatorSecret($secret);
        $entityManager->flush();

        $qrCodeContent = $googleAuthenticator->getQRContent($user);
        $qrCode = new QrCode($qrCodeContent);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $this->render('security/2fa_form.html.twig', [
            'qrCode' => $result->getDataUri(),
            'secret' => $secret,
        ]);
    }

    #[Route('/confirm-2fa', name: 'app_confirm_2fa', methods: ['POST'])]
    public function confirm2fa(
        Request $request,
        GoogleAuthenticatorInterface $googleAuthenticator,
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        UserRepository $userRepository
    ): Response {
        /** @var User $user */
        $userId = $session->get('user_id');

        if (!$userId) {
            $this->addFlash('error', 'You must be logged in to access this page.');
            return $this->redirectToRoute('app_login');
        }

        $user = $userRepository->find($userId);
        $code = $request->request->get('code');

        if ($googleAuthenticator->checkCode($user, $code)) {
            $user->setIsGoogleAuthenticatorEnabled(true);
            $entityManager->flush();
            $session->getFlashBag()->clear();
            $this->addFlash('success', '2FA enabled successfully');
            return $this->redirectToRoute('app_user_index');
        }

        $this->addFlash('error', 'Invalid code');
        return $this->redirectToRoute('app_enable_2fa');
    }
}