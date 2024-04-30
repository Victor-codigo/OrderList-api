<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupGetUsers;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetUsers\Dto\GroupGetUsersDto;

class GroupGetUsersService
{
    private PaginatorInterface $groupUsersPaginator;

    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private ModuleCommunicationInterface $moduleCommunication,
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(GroupGetUsersDto $input): array
    {
        $groupUsers = $this->getGroupUsers($input->groupId, $input->page, $input->pageItems);

        return $this->getUsersData($groupUsers, $input->filterText, $input->orderAsc);
    }

    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    private function getGroupUsers(Identifier $groupId, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $this->groupUsersPaginator = $this->userGroupRepository->findGroupUsersOrFail($groupId);
        $this->groupUsersPaginator->setPagination($page->getValue(), $pageItems->getValue());

        return iterator_to_array($this->groupUsersPaginator);
    }

    public function getPagesTotal(): int
    {
        return $this->groupUsersPaginator->getPagesTotal();
    }

    /**
     * @param UserGroup[] $groupUsers
     *
     * @throws DomainInternalErrorException
     */
    private function getUsersData(array $groupUsers, ?Filter $filterText, bool $orderAsc): array
    {
        try {
            /** @var Identifier[] $usersId */
            $usersId = array_map(
                fn (UserGroup $userGroup) => $userGroup->getUserId(),
                $groupUsers
            );

            /** @var UserGroup[] $usersAdmin */
            $usersAdmin = array_filter(
                $groupUsers,
                fn (UserGroup $userGroup) => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN)),
            );

            /** @var Identifier[] $usersAdminId */
            $usersAdminId = array_map(
                fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
                $usersAdmin
            );

            /** @var ResponseDto $usersData */
            $usersData = $this->moduleCommunication->__invoke(
                ModuleCommunicationFactory::userGet($usersId)
            );

            $usersFilteredByNameData = $this->filterUsersByName($usersData->data, $filterText);

            if (empty($usersFilteredByNameData)) {
                throw new DBNotFoundException();
            }

            array_walk(
                $usersFilteredByNameData,
                fn (array &$userData) => $userData['admin'] = in_array($userData['id'], $usersAdminId)
            );

            return $this->sortUsersByName($usersFilteredByNameData, $orderAsc);
        } catch (DBNotFoundException $th) {
            throw $th;
        } catch (\Throwable $th) {
            throw new DomainInternalErrorException();
        }
    }

    private function filterUsersByName(array $usersData, ?Filter $filterText): array
    {
        if (null === $filterText || $filterText->isNull()) {
            return $usersData;
        }

        $pattern = preg_quote($filterText->getValue());

        return match ($filterText->getFilter()->getValue()) {
            FILTER_STRING_COMPARISON::EQUALS => array_filter(
                $usersData,
                fn (array $userData) => mb_ereg("^{$pattern}$", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::STARTS_WITH => array_filter(
                $usersData,
                fn (array $userData) => mb_ereg("^{$pattern}.*", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::ENDS_WITH => array_filter(
                $usersData,
                fn (array $userData) => mb_ereg(".*{$pattern}$", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::CONTAINS => array_filter(
                $usersData,
                fn (array $userData) => mb_ereg(".*{$pattern}.*", $userData['name'])
            ),
        };
    }

    private function sortUsersByName(array $usersData, bool $orderAsc): array
    {
        usort($usersData, function (array $userData1, array $userData2) use ($orderAsc) {
            if ($userData1['name'] === $userData2['name']) {
                return 0;
            }

            if ($orderAsc) {
                return $userData1['name'] < $userData2['name'] ? -1 : 1;
            }

            return $userData1['name'] > $userData2['name'] ? -1 : 1;
        });

        return $usersData;
    }
}
