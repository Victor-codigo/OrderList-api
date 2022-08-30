<?php

declare(strict_types=1);

namespace User\Domain\Service\Create;

use User\Adapter\Database\Orm\Doctrine\Repository\UserRepository;
use User\Domain\Model\User;
use User\Domain\Service\Create\Dto\UserCreateInputDto;

class UserCreate
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(UserCreateInputDto $userDto)
    {
        $userNew = new User($userDto->email, $userDto->password, $userDto->name);
    }
}
