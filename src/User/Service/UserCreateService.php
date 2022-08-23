<?php

declare(strict_types=1);

namespace App\User\Service;

use App\Orm\Entity\User;
use App\User\Dao\UserCreateDao;
use App\User\Repository\UserRepository;

class UserCreateService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(UserCreateDao $user): User
    {
        $user_new = new User($user);

        $this->userRepository->save($user_new);

        return $user_new;
    }
}