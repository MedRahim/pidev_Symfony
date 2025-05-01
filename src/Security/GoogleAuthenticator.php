<?php

// src/Security/GoogleAuthenticator.php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check'
            && $request->query->has('code');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();

                $existingUser = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    // Update last login information
                    $existingUser->setLastLoginDate(new \DateTime());
                    $this->entityManager->flush();
                    return $existingUser;
                }

                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['Email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email)
                        ->setGoogleId($googleUser->getId())
                        ->setName($googleUser->getName())
                        ->setAvatar($googleUser->getAvatar())
                        ->setIsVerified(true)
                        ->setCreatedAt(new \DateTime())
                        ->setBirthday(new \DateTime('1990-01-01'))
                        ->setCIN('00000000')
                        ->setPassword(bin2hex(random_bytes(16)))
                        ->setPhone('0000000000')
                        ->setAddress('to be updated')
                        ->setGoogleAuthenticatorSecret(null);

                    $user->setIsGoogleAuthenticatorEnabled('false');
                } else {
                    // Merge Google account with existing account
                    $user->setGoogleId($googleUser->getId());
                }

                // Update common fields for both new and existing users
                $user->setLastLoginDate(new \DateTime())
                    ->setFailedLoginAttempts(0);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setLastLoginDate(new \DateTime());
            $this->entityManager->flush();
        }

        // Handle CIN check first
        if ($user instanceof User && $user->getCIN() === '00000000') {
            $request->getSession()->set('pending_user_id', $user->getId());
            return new RedirectResponse(
                $this->router->generate('app_user_complete_profile')
            );
        }

        // Set user ID in session
        $request->getSession()->set('user_id', $user->getId());

        // ✅ Check if 2FA is enabled and redirect if necessary
        if (
            $user instanceof TwoFactorInterface &&
            $user->isGoogleAuthenticatorEnabled() &&
            !$request->getSession()->get('2fa_verified', false)
        ) {
            return new RedirectResponse(
                $this->router->generate('2fa_verify_code')
            );
        }

        // ✅ Default success redirect
        return new RedirectResponse(
            $this->router->generate('app_user_index')
        );
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        $url = $this->router->generate('connect_google_start', ['reauth' => time()]);
        return new RedirectResponse($url);    }
}