<?php

declare(strict_types=1);

namespace User\Application\UserRegister;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserRegister\Dto\UserRegisterInputDto;
use User\Application\UserRegister\Dto\UserRegisterOutputDto;
use User\Application\UserRegister\Exception\EmailAlreadyExistsException;
use User\Domain\Event\UserPreRegistered\UserPreRegisteredEvent;
use User\Domain\Model\User;
use User\Domain\Port\PasswordHasher\PasswordHasherInterface;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Port\User\UserInterface;

class UserRegisterUseCase extends ServiceBase
{
    private UserRepositoryInterface $repository;
    private EventDispatcherInterface $eventDispatcher;
    private ValidationInterface $validation;
    private PasswordHasherInterface&UserInterface $passwordHasher;

    public function __construct(
        UserRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        ValidationInterface $validation,
        PasswordHasherInterface&UserInterface $passwordHasher,
    ) {
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
        $this->validation = $validation;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @throws ValueObjectValidationException
     */
    public function __invoke(UserRegisterInputDto $userDto): UserRegisterOutputDto
    {
        try {
            $this->validation($userDto);
            $user = $this->createUser($userDto);
            $this->repository->save($user);

            $this->eventsRegisteredDispatch($this->eventDispatcher, $user->getEventsRegistered());
        } catch (DBUniqueConstraintException) {
            throw EmailAlreadyExistsException::fromMessage('The email already exists');
        } catch (DBConnectionException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }

        return $this->createOutputDto($user);
    }

    protected function validation(ServiceInputDtoInterface $userDto): void
    {
        $errorList = $userDto->validate($this->validation);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createUser(UserRegisterInputDto $userDto): User
    {
        $id = ValueObjectFactory::createIdentifier($this->repository->generateId());
        $user = new User(
            $id,
            $userDto->email,
            $userDto->password,
            $userDto->name,
            $userDto->roles
        );
        $user->setUserPreRegisteredEventData(
            new UserPreRegisteredEvent($id, $userDto->email, $userDto->userRegisterEmailConfirmationUrl)
        );

        $this->passwordHasher->setUser($user);
        $this->passwordHasher->passwordHash($userDto->password->getValue());

        return $user;
    }

    private function createOutputDto(User $user): UserRegisterOutputDto
    {
        return new UserRegisterOutputDto($user->getId());
    }
}
