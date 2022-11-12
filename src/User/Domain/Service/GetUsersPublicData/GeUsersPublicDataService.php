<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersPublicData;

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
    public function __invoke(GetUsersPublicDataDto $usersDto): GetUsersPablicDataOutputDto
    {
        /** @var User[] $users */
        $users = $this->userRepository->findUsersByIdOrFail($usersDto->usersId);
        $usersData = $this->getUserPublicData($users);

        return new GetUsersPablicDataOutputDto($usersData);
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
}
