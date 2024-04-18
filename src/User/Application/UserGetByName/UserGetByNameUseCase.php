<?php

declare(strict_types=1);

namespace User\Application\UserGetByName;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectBase;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Struct\SCOPE;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\User\USER_ROLES;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserGetByName\Dto\UserGetByNameInputDto;
use User\Application\UserGetByName\Dto\UserGetByNameOutputDto;
use User\Application\UserGetByName\Exception\GetUsersByNameNotFoundException;
use User\Domain\Model\User;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataOutputDto;
use User\Domain\Service\GetUsersPublicData\GeUsersPublicDataService;

class UserGetByNameUseCase extends ServiceBase
{
    public function __construct(
        private GeUsersPublicDataService $geUsersPublicDataService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(UserGetByNameInputDto $input): UserGetByNameOutputDto
    {
        $this->validation($input);

        try {
            $userData = $this->geUsersPublicDataService->__invoke(
                $this->createGetUsersPublicDataDto($input->usersName),
                $this->getScope($input->userSession, $input->usersName)
            );

            return $this->createUserGetByNameOutputDto($userData);
        } catch (DBNotFoundException) {
            throw GetUsersByNameNotFoundException::fromMessage('Users not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(UserGetByNameInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function getScope(User $userSession, array $usersName): SCOPE
    {
        if ($userSession->getRoles()->has(new Rol(USER_ROLES::ADMIN))) {
            return SCOPE::PRIVATE;
        } elseif (count($usersName) > 1) {
            return SCOPE::PUBLIC;
        } elseif ($userSession->getName()->equalTo(reset($usersName))) {
            return SCOPE::PRIVATE;
        }

        return SCOPE::PUBLIC;
    }

    /**
     * @param NameWithSpaces[] $usersName
     */
    private function createGetUsersPublicDataDto(array $usersName): GetUsersPublicDataDto
    {
        return new GetUsersPublicDataDto($usersName);
    }

    private function createUserGetByNameOutputDto(GetUsersPublicDataOutputDto $userData): UserGetByNameOutputDto
    {
        $userDataPlain = [];
        foreach ($userData->usersData as $userNumber => $userData) {
            foreach ($userData as $property => $data) {
                if ($data instanceof Roles) {
                    $userDataPlain[$userNumber][$property] = array_map(fn (Rol $rol) => $rol->getValue()->value, $data->getValue());
                    continue;
                } elseif ($data instanceof \DateTime) {
                    $userDataPlain[$userNumber][$property] = $data->format('Y-m-d H:i:s');
                    continue;
                } elseif ($data instanceof ValueObjectBase) {
                    $userDataPlain[$userNumber][$property] = $data->getValue();
                    continue;
                }

                $userDataPlain[$userNumber][$property] = $data;
            }
        }

        return new UserGetByNameOutputDto($userDataPlain);
    }
}
