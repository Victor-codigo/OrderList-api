<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData;

use Common\Domain\Struct\SCOPE;
use User\Domain\Port\Repository\ProfileRepositoryInterface;
use User\Domain\Service\GetUsersProfilePublicData\Dto\GetUsersProfilePublicDataDto;
use User\Domain\Service\GetUsersProfilePublicData\Dto\GetUsersProfilePublicDataOutputDto;

class GetUsersProfilePublicDataService
{
    private ProfileRepositoryInterface $profileRepository;

    public function __construct(ProfileRepositoryInterface $profileReposistory)
    {
        $this->profileRepository = $profileReposistory;
    }

    public function __invoke(GetUsersProfilePublicDataDto $usersDto, SCOPE $scope): GetUsersProfilePublicDataOutputDto
    {
        $profiles = $this->profileRepository->findProfilesOrFail($usersDto->usersId);
        $profilesData = match ($scope) {
            SCOPE::PRIVATE => $this->getProfilesPrivateData($profiles),
            SCOPE::PUBLIC => $this->getProfilesPublicData($profiles)
        };

        return new GetUsersProfilePublicDataOutputDto($profilesData);
    }

    private function getProfilesPublicData(array $profiles): array
    {
        $profilesData = [];
        foreach ($profiles as $profile) {
            $profilesData[] = [
                'id' => $profile->getId(),
                'image' => $profile->getImage(),
            ];
        }

        return $profilesData;
    }

    /**
     * @param Profiles[] $users
     */
    private function getProfilesPrivateData(array $profiles): array
    {
        $profileData = [];
        foreach ($profiles as $profile) {
            $profileData[] = [
                'id' => $profile->getId(),
                'image' => $profile->getImage(),
            ];
        }

        return $profileData;
    }
}
