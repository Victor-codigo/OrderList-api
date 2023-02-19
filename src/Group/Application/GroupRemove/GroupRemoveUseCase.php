<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Exception;
use Group\Application\GroupRemove\Dto\GroupRemoveInputDto;
use Group\Application\GroupRemove\Dto\GroupRemoveOutputDto;
use Group\Application\GroupRemove\Exception\GroupRemoveGroupNotFoundException;
use Group\Application\GroupRemove\Exception\GroupRemovePermissionsException;
use Group\Domain\Service\GroupRemove\Dto\GroupRemoveDto;
use Group\Domain\Service\GroupRemove\GroupRemoveService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupRemoveUseCase extends ServiceBase
{
    public function __construct(
        private GroupRemoveService $groupRemoveService,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(GroupRemoveInputDto $input): GroupRemoveOutputDto
    {
        try {
            $this->validation($input);
            $this->groupRemoveService->__invoke(
                $this->createGroupRemoveDto($input)
            );

            return $this->createGroupRemoveOutputDto($input->groupId);
        } catch (DBNotFoundException) {
            throw GroupRemoveGroupNotFoundException::fromMessage('Group not found');
        } catch (ValueObjectValidationException|GroupRemovePermissionsException $e) {
            throw $e;
        } catch (Exception) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupRemovePermissionsException::fromMessage('Not permissions in this group');
        }
    }

    private function createGroupRemoveDto(GroupRemoveInputDto $input): GroupRemoveDto
    {
        return new GroupRemoveDto($input->groupId);
    }

    private function createGroupRemoveOutputDto(Identifier $groupId): GroupRemoveOutputDto
    {
        return new GroupRemoveOutputDto($groupId);
    }
}
