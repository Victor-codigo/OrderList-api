<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Struct\SCOPE;
use Common\Domain\Validation\User\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataOutputDto;

class GeUsersPublicDataService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private string $userPublicImagePath,
        private string $appProtocolAndDomain
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(GetUsersPublicDataDto $usersDto, SCOPE $scope): GetUsersPublicDataOutputDto
    {
        $users = $this->getUserFromDataBase($usersDto->users);
        $usersValid = $this->getValidUsers($users);

        $usersData = match ($scope) {
            SCOPE::PRIVATE => $this->getUserPrivateData($usersValid),
            SCOPE::PUBLIC => $this->getUserPublicData($usersValid)
        };

        return new GetUsersPublicDataOutputDto($usersData);
    }

    /**
     * @param Identifier[]|NameWithSpaces[] $users
     *
     * @return User[]
     *
     * @throws LogicException
     */
    private function getUserFromDataBase(array $users): array
    {
        if (empty($users)) {
            throw LogicException::fromMessage('You have not pass any users to find');
        }

        $this->hasUsersValid($users);

        if (reset($users) instanceof Identifier) {
            return $this->userRepository->findUsersByIdOrFail($users);
        }

        if (reset($users) instanceof NameWithSpaces) {
            return $this->userRepository->findUsersByNameOrFail($users);
        }
    }

    /**
     * @throws LogicException
     */
    private function hasUsersValid(array $users): void
    {
        $usersValid = array_filter(
            $users,
            fn ($user): bool => $user instanceof Identifier || $user instanceof NameWithSpaces
        );

        if (count($usersValid) !== count($users)) {
            throw LogicException::fromMessage('Users must be type of '.Identifier::class.' or '.NameWithSpaces::class);
        }
    }

    /**
     * @throws DBNotFoundException
     */
    private function getValidUsers(array $users): array
    {
        $usersValid = array_values(array_filter(
            $users,
            fn (User $user): bool => !$user->getRoles()->has(new Rol(USER_ROLES::NOT_ACTIVE))
                            && !$user->getRoles()->has(new Rol(USER_ROLES::DELETED))
        ));

        if (empty($usersValid)) {
            throw DBNotFoundException::fromMessage('users not found');
        }

        return $usersValid;
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
     * @param User[] $users
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
                'image' => $user->getImage()->isNull()
                    ? ValueObjectFactory::createPath(null)
                    : ValueObjectFactory::createPath("{$this->appProtocolAndDomain}{$this->userPublicImagePath}/{$user->getImage()->getValue()}"),
                'created_on' => $user->getCreatedOn(),
            ];
        }

        return $usersData;
    }
}
