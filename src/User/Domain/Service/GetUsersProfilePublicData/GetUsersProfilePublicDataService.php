<?php

declare(strict_types=1);

namespace User\Domain\Service\GetUsersProfilePublicData;

use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Struct\SCOPE;
use User\Domain\Port\Repository\ProfileRepositoryInterface;
use User\Domain\Service\GetUsersProfilePublicData\Dto\GetUsersProfilePublicDataDto;
use User\Domain\Service\GetUsersProfilePublicData\Dto\GetUsersProfilePublicDataOutputDto;

class GetUsersProfilePublicDataService
{
    private ProfileRepositoryInterface $profileRepository;
    private string $userPublicImagePath;

    public function __construct(ProfileRepositoryInterface $profileRepository, string $userPublicImagePath)
    {
        $this->profileRepository = $profileRepository;
        $this->userPublicImagePath = $userPublicImagePath;
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
            $image = $profile->getImage()->getValue();
            $profilesData[] = [
                'id' => $profile->getId(),
                'image' => null === $image ? null : ValueObjectFactory::createPath($this->userPublicImagePath.'/'.$image),
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
            $image = $profile->getImage()->getValue();
            $profileData[] = [
                'id' => $profile->getId(),
                'image' => null === $image ? null : ValueObjectFactory::createPath($this->userPublicImagePath.'/'.$image),
            ];
        }

        return $profileData;
    }
}
