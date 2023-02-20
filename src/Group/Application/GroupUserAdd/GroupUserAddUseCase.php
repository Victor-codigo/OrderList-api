<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd;

use Common\Adapter\ModuleComumication\Exception\ModuleComunicationException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleComumication\ModuleComunicationFactory;
use Common\Domain\Ports\ModuleComunication\ModuleComumunicationInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupUserAdd\Dto\GroupUserAddInputDto;
use Group\Application\GroupUserAdd\Dto\GroupUserAddOutputDto;
use Group\Application\GroupUserAdd\Exception\GroupUserAddGroupMaximunUsersNumberExcededException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddGroupNotFoundException;
use Group\Application\GroupUserAdd\Exception\GroupUserAddUsersValidationException;
use Group\Application\GroupUserRoleChange\Exception\GroupUserRoleChangePermissionException;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersMaxNumberExcededException;
use Group\Domain\Service\GroupUserAdd\GroupUserAddService;
use Group\Domain\Service\UserHasGroupAdminGrants\UserHasGroupAdminGrantsService;

class GroupUserAddUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private UserGroupRepositoryInterface $userGroupRepository,
        private UserHasGroupAdminGrantsService $userHasGroupAdminGrantsService,
        private GroupUserAddService $groupUserAddService,
        private ModuleComumunicationInterface $moduleComunication
    ) {
    }

    /**
     * @throws GroupUserAddGroupMaximunUsersNumberExcededException
     * @throws GroupUserAddGroupNotFoundException
     * @throws GroupUserAddUsersValidationException
     * @throws DomainErrorException
     */
    public function __invoke(GroupUserAddInputDto $input): GroupUserAddOutputDto
    {
        try {
            $this->validation($input);
            $usersAdded = $this->groupUserAddService->__invoke(
                $this->createGroupUserAddDto($input->groupId, $input->usersId, $input->rol)
            );

            return $this->createGroupUserAddOutputDto($usersAdded);
        } catch (GroupAddUsersMaxNumberExcededException) {
            throw GroupUserAddGroupMaximunUsersNumberExcededException::fromMessage('Group User number exceded');
        } catch (DBNotFoundException) {
            throw GroupUserAddGroupNotFoundException::fromMessage('Group not found');
        } catch (DBConnectionException|ModuleComunicationException $e) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GroupUserAddUsersValidationException
     * @throws ModuleComunicationException
     * @throws GroupUserRoleChangePermissionException
     */
    private function validation(GroupUserAddInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if (!$this->userHasGroupAdminGrantsService->__invoke($input->userSession, $input->groupId)) {
            throw GroupUserRoleChangePermissionException::fromMessage('Permissions denied');
        }

        $this->validateUsersToAdd($input->usersId);
    }

    /**
     * @throws GroupUserAddUsersValidationException
     * @throws ModuleComunicationException
     */
    private function validateUsersToAdd(array $users): void
    {
        $response = $this->moduleComunication->__invoke(
            ModuleComunicationFactory::userGet($users)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw GroupUserAddUsersValidationException::fromMessage('Wrong users');
        }
    }

    private function createGroupUserAddDto(Identifier $groupId, array $usersId, Rol $rol): GroupUserAddDto
    {
        return new GroupUserAddDto(
            $groupId,
            $usersId,
            $rol
        );
    }

    /**
     * @param UserGroup[] $usersId
     */
    private function createGroupUserAddOutputDto(array $usersId): GroupUserAddOutputDto
    {
        $users = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersId
        );

        return new GroupUserAddOutputDto($users);
    }
}
