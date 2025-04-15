<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\UserRepository;
use App\Entity\User;

class CurrentUserService
{
    private RequestStack $requestStack;
    private UserRepository $userRepository;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository)
    {
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
    }

    public function getUser(): ?User
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || !$request->getSession()->has('user_id')) {
            return null;
        }

        $userId = $request->getSession()->get('user_id');

        return $this->userRepository->find($userId);
    }
}
