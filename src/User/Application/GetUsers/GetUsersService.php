<?php

declare(strict_types=1);

namespace User\Application\GetUsers;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\GetUsers\Dto\GetUsersInputDto;
use User\Application\GetUsers\Dto\GetUsersOutputDto;
use User\Application\GetUsers\Exception\GetUsersNotFoundException;
use User\Domain\Service\GetUsersProfilePublicData\Dto\GetUsersProfilePublicDataDto;
use User\Domain\Service\GetUsersProfilePublicData\GetUsersProfilePublicDataService;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;
use User\Domain\Service\GetUsersPublicData\GeUsersPublicDataService;

class GetUsersService extends ServiceBase
{
    private GeUsersPublicDataService $geUsersPublicDataService;
    private GetUsersProfilePublicDataService $getUsersProfilePublicDataService;
    private ValidationInterface $validator;

    public function __construct(GeUsersPublicDataService $geUsersPublicDataService, GetUsersProfilePublicDataService $getUsersProfilePublicDataService, ValidationInterface $validator)
    {
        $this->geUsersPublicDataService = $geUsersPublicDataService;
        $this->getUsersProfilePublicDataService = $getUsersProfilePublicDataService;
        $this->validator = $validator;
    }

    public function __invoke(GetUsersInputDto $input): GetUsersOutputDto
    {
        $this->validation($input);

        try {
            $users = $this->geUsersPublicDataService->__invoke(
                $this->createGetUsersPublicDataDto($input->usersId)
            );

            $profiles = $this->getUsersProfilePublicDataService->__invoke(
                $this->createGetUsersProfilePublicDataDto($input->usersId)
            );

            $usersData = $this->mergeUserAndProfileData($users->usersData, $profiles->profileData);

            return $this->createGetUsersOutputDto($usersData);
        } catch (DBNotFoundException) {
            throw GetUsersNotFoundException::fromMessage('Users not found');
        }
    }

    private function validation(GetUsersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error getting users', $errorList);
        }
    }

    private function createGetUsersPublicDataDto(array $usersId): GetUsersPublicDataDto
    {
        return new GetUsersPublicDataDto($usersId);
    }

    private function createGetUsersProfilePublicDataDto(array $usersId): GetUsersProfilePublicDataDto
    {
        return new GetUsersProfilePublicDataDto($usersId);
    }

    private function createGetUsersOutputDto(array $users): GetUsersOutputDto
    {
        return new GetUsersOutputDto($users);
    }

    private function mergeUserAndProfileData(array $users, array $profiles): array
    {
        $usersArray = array_map($this->getItemPlain(...), $users);
        $profilesById = array_column(array_map($this->getItemPlain(...), $profiles), null, 'id');

        $usersData = [];
        foreach ($usersArray as $user) {
            if (!isset($profilesById[$user['id']])) {
                continue;
            }

            unset($profilesById[$user['id']]['id']);
            $usersData[] = array_merge($user, $profilesById[$user['id']]);
        }

        return $usersData;
    }

    private function getItemPlain(array $item)
    {
        $itemPlain = [];
        foreach ($item as $key => $value) {
            $itemPlain[$key] = $value->getValue();
        }

        return $itemPlain;
    }
}
