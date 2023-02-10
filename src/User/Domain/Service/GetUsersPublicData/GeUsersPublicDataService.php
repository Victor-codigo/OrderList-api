<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Struct\SCOPE;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPablicDataOutputDto;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;

class GeUsersPublicDataService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GetUsersPublicDataDto $usersDto, SCOPE $scope): GetUsersPablicDataOutputDto
    {
        /** @var User[] $users */
        $users = $this->userRepository->findUsersByIdOrFail($usersDto->usersId);
        $usersValid = $this->getValidUsers($users);

        $usersData = match ($scope) {
            SCOPE::PRIVATE => $this->getUserPrivateData($usersValid),
            SCOPE::PUBLIC => $this->getUserPublicData($usersValid)
        };

        return new GetUsersPablicDataOutputDto($usersData);
    }

    private function getValidUsers(array $users): array
    {
        return array_values(array_filter(
            $users,
            fn (User $user) => !$user->getRoles()->has(new Rol(USER_ROLES::NOT_ACTIVE))
                            && !$user->getRoles()->has(new Rol(USER_ROLES::DELETED))
        ));
    }

    /**
     * @param User[] $users
     */
    private function getUserPublicData(array $users): array
    {
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
            ];
        }

        return $usersData;
    }

    /**
     * @param Users[] $users
     */
    private function getUserPrivateData(array $users): array
    {
        $usersData = [];
        foreach ($users as $user) {
            $usersData[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'roles' => $user->getRoles(),
                'createdOn' => $user->getCreatedOn(),
            ];
        }

        return $usersData;
    }
}
