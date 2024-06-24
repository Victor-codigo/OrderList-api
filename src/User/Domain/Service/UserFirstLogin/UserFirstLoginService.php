<?php

declare(strict_types=1);

namespace User\Domain\Service\UserFirstLogin;

use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Validation\User\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\UserCreateGroup\Dto\UserCreateGroupDto;
use User\Domain\Service\UserCreateGroup\Exception\UserCreateGroupUserException;
use User\Domain\Service\UserCreateGroup\UserCreateGroupService;
use User\Domain\Service\UserFirstLogin\Dto\UserFirstLoginDto;
use User\Domain\Service\UserFirstLogin\Exception\UserFirstLoginCreateGroupException;
use User\Domain\Service\UserFirstLogin\Exception\UserFirstLoginNotificationUserRegisteredException;

class UserFirstLoginService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserCreateGroupService $userCreateGroupService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $appName,
        private string $systemKey
    ) {
    }

    /**
     * @throws UserFirstLoginCreateGroupException
     * @throws DomainErrorException
     */
    public function __invoke(UserFirstLoginDto $input): void
    {
        if (!$this->isFirstLogin($input->user)) {
            return;
        }

        try {
            $this->userCreateGroupService->__invoke(
                $this->createUserCreateGroupDto($input->user->getName())
            );

            $this->changeRoleToUser($input->user);
            $this->createNotificationUserRegistered($input->user, $this->appName, $this->systemKey);
        } catch (UserCreateGroupUserException $e) {
            throw UserFirstLoginCreateGroupException::fromMessage('Could not create the user\'s group');
        } catch (\Exception $e) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    private function isFirstLogin(User $user): bool
    {
        if ($user->getRoles()->has(ValueObjectFactory::createRol(USER_ROLES::USER_FIRST_LOGIN))) {
            return true;
        }

        return false;
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    private function changeRoleToUser(User $user): void
    {
        $user->setRoles(ValueObjectFactory::createRoles([
            ValueObjectFactory::createRol(USER_ROLES::USER),
        ]));

        $this->userRepository->save($user);
    }

    private function createUserCreateGroupDto(NameWithSpaces $userName): UserCreateGroupDto
    {
        return new UserCreateGroupDto($userName);
    }

    /**
     * @throws UserFirstLoginNotificationUserRegisteredException
     */
    private function createNotificationUserRegistered(User $user, string $appName, string $systemKey): void
    {
        $notificationData = ModuleCommunicationFactory::notificationCreateUserRegistered(
            [$user->getId()],
            $user->getName(),
            $appName,
            $systemKey
        );

        $response = $this->moduleCommunication->__invoke($notificationData);

        if (!empty($response->getErrors())) {
            throw UserFirstLoginNotificationUserRegisteredException::fromMessage('An error was ocurred when trying to send the notification: user registered');
        }
    }
}
