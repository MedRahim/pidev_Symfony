<?php

// src/Security/OAuthUserProvider.php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['Email' => $identifier]);
    }

    public function loadUserByOAuthUserResponse($resourceOwner, $userData)
    {
        if ($resourceOwner === 'google' && $userData instanceof GoogleUser) {
            $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['googleId' => $userData->getId()]);

            if (!$user) {
                $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['Email' => $userData->getEmail()]);
            }

            if (!$user) {
                $user = new User();
                $user->setEmail($userData->getEmail());
                $user->setGoogleId($userData->getId());
                $user->setName($userData->getName());
                $user->setAvatar($userData->getAvatar());
                $user->setIsVerified(true);
                $user->setRoles(['ROLE_USER']);

                // Set default values for required fields
                $user->setCIN('00000000');
                $user->setPhone('+000000000');
                $user->setAddress('To be updated');
                $user->setBirthday(new \DateTime('1990-01-01'));
                $user->setPassword('googleauth');

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

            return $user;
        }

        throw new \RuntimeException(sprintf('No OAuth2 provider found for "%s".', $resourceOwner));
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}