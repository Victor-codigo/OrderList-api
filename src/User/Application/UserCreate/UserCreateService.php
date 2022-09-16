<?php

declare(strict_types=1);

namespace User\Application\UserCreate;

use Common\Domain\Exception\ValueObjectValidationException;
use Common\Domain\Validation\IValidation;
use User\Application\UserCreate\Dto\UserCreateInputDto;
use User\Domain\Model\User;
use User\Domain\Port\User\UserInterface;
use User\Domain\Repository\IUserRepository;

class UserCreateService
{
    private IUserRepository $repository;
    private IValidation $validation;

    public function __construct(IUserRepository $repository, IValidation $validation)
    {
        $this->repository = $repository;
        $this->validation = $validation;
    }

    /**
     * @throws ValueObjectValidationException
     */
    public function __invoke(UserCreateInputDto $userDto, UserInterface $user): void
    {
        $errors = $userDto->validate($this->validation);

        if (!empty($errors)) {
            throw ValueObjectValidationException::createFromArray($errors);
        }

        $user
            ->setUser(User::createFromDto($userDto))
            ->passwordHash();

        $this->repository->save($user->getUser());
    }
}
