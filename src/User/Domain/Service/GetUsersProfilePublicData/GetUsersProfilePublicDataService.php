<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData;

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

    public function __invoke(GetUsersProfilePublicDataDto $usersDto): GetUsersProfilePublicDataOutputDto
    {
        $profiles = $this->profileRepository->findProfilesOrFail($usersDto->usersId);
        $profilesData = $this->getProfilesPublicData($profiles);

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
}
