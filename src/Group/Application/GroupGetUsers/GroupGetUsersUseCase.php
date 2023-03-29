<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersOutputDto;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersGroupNotFoundException;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersUserNotInTheGroupException;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class GroupGetUsersUseCase extends ServiceBase
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidationInterface $validator
    ) {
    }

    /**
     * @throws GroupGetUsersUserNotInTheGroupException
     * @throws GroupGetUsersGroupNotFoundException
     * @throws ValueObjectValidationException
     * @throws DomainErrorException
     */
    public function __invoke(GroupGetUsersInputDto $input): GroupGetUsersOutputDto
    {
        $this->validation($input);

        try {
            $groupUsers = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
            $this->validateIsUserSessionInGroup($input->groupId, $input->userSession->getId());
            $usersData = $this->getUsersData($groupUsers, $input->page, $input->pageItems);

            return $this->createGroupGetUsersOutputDto($usersData);
        } catch (GroupGetUsersUserNotInTheGroupException $e) {
            throw $e;
        } catch (DBNotFoundException) {
            throw GroupGetUsersGroupNotFoundException::fromMessage('Group not found');
        } catch (\Throwable $e) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GroupGetUsersUserNotInTheGroupException
     */
    private function validation(GroupGetUsersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param UserGroup[] $groupUsers
     *
     * @throws GroupGetUsersUserNotInTheGroupException
     */
    private function validateIsUserSessionInGroup(Identifier $groupId, Identifier $userSessionId): void
    {
        try {
            $this->userGroupRepository->findGroupUsersByUserIdOrFail($groupId, [$userSessionId]);
        } catch (DBNotFoundException) {
            throw GroupGetUsersUserNotInTheGroupException::fromMessage('You have not permissions');
        }
    }

    /**
     * @throws Error400Exception
     * @throws ModuleCommunicationException
     * @throws \ValueError
     */
    private function getUsersData(PaginatorInterface $usersGroup, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $usersGroup->setPagination($page->getValue(), $pageItems->getValue());
        $usersId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
            iterator_to_array($usersGroup)
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::userGet($usersId)
        );

        return $response->getData();
    }

    private function createGroupGetUsersOutputDto(array $users): GroupGetUsersOutputDto
    {
        return new GroupGetUsersOutputDto($users);
    }
}
