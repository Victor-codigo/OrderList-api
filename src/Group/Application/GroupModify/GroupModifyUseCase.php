<?php

declare(strict_types=1);

namespace Group\Application\GroupModify;

use App\Group\Application\GroupModify\Exception\GroupModifyPermissionsException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Exception;
use Group\Application\GroupModify\Dto\GroupModifyInputDto;
use Group\Application\GroupModify\Dto\GroupModifyOutputDto;
use Group\Application\GroupModify\Exception\GroupModifyGroupNotFoundException;
use Group\Domain\Service\GroupModify\Dto\GroupModifyDto;
use Group\Domain\Service\GroupModify\GroupModifyService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupModifyUseCase extends ServiceBase
{
    public function __construct(
        private GroupModifyService $groupModifyService,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(GroupModifyInputDto $input): GroupModifyOutputDto
    {
        try {
            $this->validation($input);

            $this->groupModifyService->__invoke(
                $this->createGroupModifyDto($input)
            );

            return $this->createGroupModifyOutputDto($input->groupId);
        } catch (ValueObjectValidationException|GroupModifyPermissionsException $e) {
            throw $e;
        } catch (DBNotFoundException) {
            throw GroupModifyGroupNotFoundException::fromMessage('Group not found');
        } catch (Exception) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GroupModifyPermissionsException
     */
    private function validation(GroupModifyInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupModifyPermissionsException::fromMessage('Not permissions in this group');
        }
    }

    private function createGroupModifyDto(GroupModifyInputDto $input): GroupModifyDto
    {
        return new GroupModifyDto($input->groupId, $input->name, $input->description);
    }

    private function createGroupModifyOutputDto(Identifier $modified): GroupModifyOutputDto
    {
        return new GroupModifyOutputDto($modified);
    }
}
