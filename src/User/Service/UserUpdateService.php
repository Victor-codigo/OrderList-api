<?php

declare(strict_types=1);

namespace User\Service;

use App\Orm\Entity\User;
use User\Dao\UserUpdateDao;
use User\Repository\UserRepository;

class UserUpdateService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(string $id, UserUpdateDao $user): User
    {
        $userModify = $this->userRepository->findById($id);

        if (null !== $userModify) {
            $userModify->setName($user->getName())
                       ->setEmail($user->getEmail())
                       ->setPassword($user->getPassword());

            $this->userRepository->save($userModify);
        }

        return $userModify;
    }
}
