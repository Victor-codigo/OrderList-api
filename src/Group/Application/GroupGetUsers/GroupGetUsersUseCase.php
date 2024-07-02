<?php

declare(strict_types=1);

namespace Group\Application\GroupGetUsers;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersOutputDto;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersGroupNotFoundException;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersUserNotInTheGroupException;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetUsers\Dto\GroupGetUsersDto;
use Group\Domain\Service\GroupGetUsers\GroupGetUsersService;

class GroupGetUsersUseCase extends ServiceBase
{
    public function __construct(
        private GroupGetUsersService $groupGetUsersService,
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
            $this->validateIsUserSessionInGroup($input->groupId, $input->userSession->getId());
            $usersData = $this->groupGetUsersService->__invoke(
                $this->createGroupGetUsersDto($input)
            );

            return $this->createGroupGetUsersOutputDto(
                $usersData,
                $input->page,
                $this->groupGetUsersService->getPagesTotal()
            );
        } catch (GroupGetUsersUserNotInTheGroupException $e) {
            throw $e;
        } catch (DBNotFoundException) {
            throw GroupGetUsersGroupNotFoundException::fromMessage('Group not found');
        } catch (\Throwable) {
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

    private function createGroupGetUsersDto(GroupGetUsersInputDto $input): GroupGetUsersDto
    {
        return new GroupGetUsersDto($input->groupId, $input->page, $input->pageItems, $input->filterSection, $input->filterText, $input->orderAsc);
    }

    private function createGroupGetUsersOutputDto(array $users, PaginatorPage $page, int $pagesTotal): GroupGetUsersOutputDto
    {
        return new GroupGetUsersOutputDto($users, $page, $pagesTotal);
    }
}
