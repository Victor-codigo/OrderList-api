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
    /**
     * @var PaginatorInterface<int, UserGroup>
     */
    private PaginatorInterface $groupUsersPaginator;

    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private ModuleCommunicationInterface $moduleCommunication,
    ) {
    }

    /**
     * @return array<int, array{
     *  id: string,
     *  name: string,
     *  image: string|null,
     *  created_on: string|null,
     *  admin: bool
     * }>
     *
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
     * @return array<int, array{
     *  id: string,
     *  name: string,
     *  image: string|null,
     *  created_on: string|null,
     *  admin: bool
     * }>
     *
     * @throws DomainInternalErrorException
     */
    private function getUsersData(array $groupUsers, ?Filter $filterText, bool $orderAsc): array
    {
        try {
            /** @var Identifier[] $usersId */
            $usersId = array_map(
                fn (UserGroup $userGroup): Identifier => $userGroup->getUserId(),
                $groupUsers
            );

            /** @var UserGroup[] $usersAdmin */
            $usersAdmin = array_filter(
                $groupUsers,
                fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN)),
            );

            /** @var Identifier[] $usersAdminId */
            $usersAdminId = array_map(
                fn (UserGroup $userGroup): ?string => $userGroup->getUserId()->getValue(),
                $usersAdmin
            );

            /** @var ResponseDto{
             *  data: array<int, array{
             *      id: string,
             *      email: string,
             *      name: string,
             *      roles: string[],
             *      created_on: string,
             *      image: string|null
             *      admin: bool
             * }> $usersData
             */
            $usersData = $this->moduleCommunication->__invoke(
                ModuleCommunicationFactory::userGet($usersId)
            );

            $usersFilteredByNameData = $this->filterUsersByName($usersData->data, $filterText);

            if (empty($usersFilteredByNameData)) {
                throw new DBNotFoundException();
            }

            array_walk(
                $usersFilteredByNameData,
                fn (array &$userData): bool => $userData['admin'] = in_array($userData['id'], $usersAdminId)
            );

            $usersSortedByName = $this->sortUsersByName($usersFilteredByNameData, $orderAsc);

            return $this->getUserData($usersSortedByName);
        } catch (DBNotFoundException $th) {
            throw $th;
        } catch (\Throwable) {
            throw new DomainInternalErrorException();
        }
    }

    /**
     * @param array<int, array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string,
     *  created_on: string|null,
     *  image: string|null,
     *  admin: bool
     * }> $usersData
     *
     * @return array<int, array{
     *  id: string,
     *  name: string,
     *  image: string|null,
     *  created_on: string|null,
     *  admin: bool
     * }>
     */
    private function getUserData(array $usersData): array
    {
        return array_map(
            fn (array $userData): array => [
                'id' => $userData['id'],
                'name' => $userData['name'],
                'image' => $userData['image'] ?? null,
                'created_on' => $userData['created_on'] ?? null,
                'admin' => $userData['admin'],
            ],
            $usersData
        );
    }

    /**
     * @param array<int, array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string,
     *  created_on: string,
     *  image: string|null,
     *  admin: bool
     * }> $usersData
     *
     * @return array<int, array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string,
     *  created_on: string,
     *  image: string|null,
     *  admin: bool
     * }>
     */
    private function filterUsersByName(array $usersData, ?Filter $filterText): array
    {
        if (null === $filterText || $filterText->isNull()) {
            return $usersData;
        }

        $pattern = preg_quote((string) $filterText->getValue());

        return match ($filterText->getFilter()->getValue()) {
            FILTER_STRING_COMPARISON::EQUALS => array_filter(
                $usersData,
                fn (array $userData): bool => $this->matchStringCiAi("^{$pattern}$", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::STARTS_WITH => array_filter(
                $usersData,
                fn (array $userData): bool => $this->matchStringCiAi("^{$pattern}.*", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::ENDS_WITH => array_filter(
                $usersData,
                fn (array $userData): bool => $this->matchStringCiAi(".*{$pattern}$", $userData['name'])
            ),
            FILTER_STRING_COMPARISON::CONTAINS => array_filter(
                $usersData,
                fn (array $userData): bool => $this->matchStringCiAi(".*{$pattern}.*", $userData['name'])
            ),
        };
    }

    private function matchStringCiAi(string $pattern, string $value): bool
    {
        $patternWithoutAccents = iconv('utf-8', 'ASCII//TRANSLIT', $pattern);
        $valueWithoutAccents = iconv('utf-8', 'ASCII//TRANSLIT', $value);

        return mb_eregi($patternWithoutAccents, $valueWithoutAccents);
    }

    /**
     * @param array<int, array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string,
     *  created_on: string,
     *  image: string|null,
     *  admin: bool
     * }> $usersData
     *
     * @return array<int, array{
     *  id: string,
     *  email: string,
     *  name: string,
     *  roles: string,
     *  created_on: string,
     *  image: string|null,
     *  admin: bool
     * }>
     */
    private function sortUsersByName(array $usersData, bool $orderAsc): array
    {
        usort($usersData, function (array $userData1, array $userData2) use ($orderAsc): int {
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
