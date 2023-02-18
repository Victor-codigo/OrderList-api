<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Exception;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveInputDto;
use Group\Application\GroupUserRemove\Dto\GroupUserRemoveOutputDto;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupEmptyException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdminException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemoveGrouporUsersNotFoundException;
use Group\Application\GroupUserRemove\Exception\GroupUserRemovePermissionsException;
use Group\Domain\Service\GroupUserRemove\Dto\GroupUserRemoveDto;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveEmptyException;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdmin;
use Group\Domain\Service\GroupUserRemove\GroupUserRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupUserRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private GroupUserRemoveService $groupUserRemoveService
    ) {
    }

    public function __invoke(GroupUserRemoveInputDto $input): GroupUserRemoveOutputDto
    {
        try {
            $this->validation($input);
            $usersRemovedId = $this->groupUserRemoveService->__invoke(
                $this->createGroupUserRemoveDto($input)
            );

            return $this->createGroupUserRemoveOutputDto($usersRemovedId);
        } catch (GroupUserRemoveEmptyException) {
            throw GroupUserRemoveGroupEmptyException::fromMessage('Cannot remove all users form a group');
        } catch (GroupUserRemoveGroupWithoutAdmin) {
            throw GroupUserRemoveGroupWithoutAdminException::fromMessage('Cannot remove all admins form a group');
        } catch (DBNotFoundException) {
            throw GroupUserRemoveGrouporUsersNotFoundException::fromMessage('Group or users not found');
        } catch (GroupUserRemovePermissionsException|ValueObjectValidationException $e) {
            throw $e;
        } catch (Exception) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupUserRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupUserRemovePermissionsException::fromMessage('Not permissions in this group');
        }
    }

    private function createGroupUserRemoveDto(GroupUserRemoveInputDto $input): GroupUserRemoveDto
    {
        return new GroupUserRemoveDto($input->groupId, $input->usersId);
    }

    private function createGroupUserRemoveOutputDto(array $usersRemovedId): GroupUserRemoveOutputDto
    {
        return new GroupUserRemoveOutputDto($usersRemovedId);
    }
}
