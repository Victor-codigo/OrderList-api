<?php

declare(strict_types=1);

namespace Group\Application\GroupGetDataByName;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetDataByName\Dto\GroupGetDataByNameInputDto;
use Group\Application\GroupGetDataByName\Dto\GroupGetDataByNameOutputDto;
use Group\Application\GroupGetDataByName\Exception\GroupGetDataByNameGroupNotFoundException;
use Group\Application\GroupGetData\Exception\GroupGetDataUserNotBelongsToTheGroupException;
use Group\Domain\Service\GroupGetDataByName\Dto\GroupGetDataByNameDto;
use Group\Domain\Service\GroupGetDataByName\GroupGetDataByNameService;

class GroupGetDataByNameUseCase extends ServiceBase
{
    public function __construct(
        private GroupGetDataByNameService $GroupGetDataByNameService,
        private ValidationInterface $validator,
        private GroupGetDataByNameService $groupGetDataByNameService,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(GroupGetDataByNameInputDto $input): GroupGetDataByNameOutputDto
    {
        $this->validation($input);

        try {
            $groupData = $this->groupGetDataByNameService->__invoke(
                $this->createGroupGetDataByNameDto($input)
            );
            $this->validateGroupAndUserService->__invoke(
                ValueObjectFactory::createIdentifier($groupData['group_id'])
            );

            return $this->createGroupGetDataByNameOutputDto($groupData);
        } catch (DBNotFoundException) {
            throw GroupGetDataByNameGroupNotFoundException::fromMessage('Group not found');
        } catch (ValidateGroupAndUserException) {
            throw GroupGetDataUserNotBelongsToTheGroupException::fromMessage('You not belong to the group');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupGetDataByNameInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createGroupGetDataByNameDto(GroupGetDataByNameInputDto $input): GroupGetDataByNameDto
    {
        return new GroupGetDataByNameDto($input->groupName, $input->userSession->getImage());
    }

    private function createGroupGetDataByNameOutputDto(array $groupData): GroupGetDataByNameOutputDto
    {
        return new GroupGetDataByNameOutputDto($groupData);
    }
}
