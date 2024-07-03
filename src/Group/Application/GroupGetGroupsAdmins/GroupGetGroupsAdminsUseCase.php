<?php

declare(strict_types=1);

namespace Group\Application\GroupGetGroupsAdmins;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetAdmins\Exception\GroupGetAdminsGroupNotFoundException;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsInputDto;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsOutputDto;
use Group\Application\GroupGetGroupsAdmins\Exception\GroupGetGroupsAdminsNotFoundException;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;

class GroupGetGroupsAdminsUseCase extends ServiceBase
{
    private const int USERS_GROUP_PAGE_ITEMS = AppConfig::ENDPOINT_GROUP_GET_GROUPS_ADMINS_MAX;

    public function __construct(
        private ValidationInterface $validator,
        private UserGroupRepositoryInterface $userGroupRepository
    ) {
    }

    /**
     * @throws GroupGetAdminsGroupNotFoundException
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(GroupGetGroupsAdminsInputDto $input): GroupGetGroupsAdminsOutputDto
    {
        $this->validation($input);

        try {
            $groupUsersPaginator = $this->userGroupRepository->findGroupsUsersOrFail($input->groupsId, GROUP_ROLES::ADMIN);
            $groupUsersPaginator->setPagination($input->page->getValue(), $input->pageItems->getValue());

            $usersGroup = iterator_to_array($groupUsersPaginator);
            $usersGroupedByGroupId = $this->setUsersByGroup($usersGroup);

            return $this->createGroupGetAdminsOutputDto($usersGroupedByGroupId, $input->page, $groupUsersPaginator->getPagesTotal());
        } catch (DBNotFoundException) {
            throw GroupGetGroupsAdminsNotFoundException::fromMessage('Groups not Found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(GroupGetGroupsAdminsInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param UserGroup[] $groupsUsers
     *
     * @return array<string, array<string>>
     */
    private function setUsersByGroup(array $groupsUsers): array
    {
        $usersGroupedByGroupId = [];
        foreach ($groupsUsers as $userGroup) {
            if (!array_key_exists($userGroup->getGroupId()->getValue(), $usersGroupedByGroupId)) {
                $usersGroupedByGroupId[$userGroup->getGroupId()->getValue()] = [];
            }

            $usersGroupedByGroupId[$userGroup->getGroupId()->getValue()][] = $userGroup->getUserId()->getValue();
        }

        return $usersGroupedByGroupId;
    }

    private function createGroupGetAdminsOutputDto(array $groupAdminsIds, PaginatorPage $page, int $pagesTotal): GroupGetGroupsAdminsOutputDto
    {
        return new GroupGetGroupsAdminsOutputDto($groupAdminsIds, $page, $pagesTotal);
    }
}
