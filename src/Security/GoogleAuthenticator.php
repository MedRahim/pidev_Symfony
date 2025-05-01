<?php

// src/Security/GoogleAuthenticator.php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
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

                // 1. Check if user already exists with this Google ID
                $existingUser = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleUser->getId()]);

                if ($existingUser) {
                    return $existingUser;
                }

                // 2. Check if user exists with this email
                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['Email' => $email]);

                if (!$user) {
                    // 3. Create new user
                    $user = new User();
                    $user->setEmail($email);
                    $user->setGoogleId($googleUser->getId());
                    $user->setName($googleUser->getName());
                    $user->setAvatar($googleUser->getAvatar());
                    $user->setIsVerified(true);
                    $user->setCreatedAt(new \DateTime());
                    $birthday = new \DateTime('1990-01-01');
                    $user->setBirthday($birthday);

                    // Set default values for required fields
                    $user->setCIN('00000000'); // Temporary value - prompt user to update
                    $tempPassword = bin2hex(random_bytes(16)); // 32-character random string
                    $user->setPassword($tempPassword);
                    $user->setPhone('0000000000');
                    $user->setAddress('to be updated');

                } else {
                    // 4. Update existing user with Google ID
                    $user->setGoogleId($googleUser->getId());
                }

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Update last login date
        $user = $token->getUser();

        if ($user instanceof User) {
            $user->setLastLoginDate(new \DateTime());
            $this->entityManager->flush();
        }

        // Redirect if CIN is temporary
        if ($user->getCIN() === '00000000') {

            $request->getSession()->set('pending_user_id', $user->getId());
            return new RedirectResponse(
                $this->router->generate('app_user_complete_profile')
            );
        }

        $request->getSession()->set('user_id', $user->getId());
        return new RedirectResponse(
            $this->router->generate('home') // Change to your desired route
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