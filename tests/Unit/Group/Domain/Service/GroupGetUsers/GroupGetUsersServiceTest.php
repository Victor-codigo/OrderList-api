<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupGetUsers;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\User\USER_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetUsers\Dto\GroupGetUsersDto;
use Group\Domain\Service\GroupGetUsers\GroupGetUsersService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class GroupGetUsersServiceTest extends TestCase
{
    private GroupGetUsersService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|PaginatorInterface $pagination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->pagination = $this->createMock(PaginatorInterface::class);
        $this->object = new GroupGetUsersService(
            $this->userGroupRepository,
            $this->moduleCommunication
        );
    }

    /**
     * @return User[]
     */
    private function getUsers(): array
    {
        return [
            User::fromPrimitives(
                'user 1 id',
                'user 1 email',
                'user 1 password',
                'user 1 name',
                [USER_ROLES::USER]
            ),
            User::fromPrimitives(
                'user 2 id',
                'user 2 email',
                'user 2 password',
                'user 2 name',
                [USER_ROLES::USER]
            ),
            User::fromPrimitives(
                'user 3 id',
                'user 3 email',
                'user 3 password',
                'user 3 name',
                [USER_ROLES::USER]
            ),
        ];
    }

    /**
     * @return UserGroup[]
     */
    private function getGroupUsersData(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(
                'group 1 id',
                'user 1 id',
                [GROUP_ROLES::USER],
                $group
            ),
            UserGroup::fromPrimitives(
                'group 1 id',
                'user 2 id',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group 1 id',
                'user 3 id',
                [GROUP_ROLES::USER],
                $group
            ),
        ];
    }

    /**
     * @param User[] $usersExpected
     */
    private function assertUserIsOk(array $usersExpected, array $users): void
    {
        $this->assertCount(count($usersExpected), $users);

        foreach ($users as $key => $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('password', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('roles', $user);

            $this->assertEquals($usersExpected[$key]->getId()->getvalue(), $user['id']);
            $this->assertEquals($usersExpected[$key]->getEmail()->getValue(), $user['email']);
            $this->assertEquals($usersExpected[$key]->getPassword()->getValue(), $user['password']);
            $this->assertEquals($usersExpected[$key]->getName()->getValue(), $user['name']);
            $this->assertEquals(array_map(fn (Rol $rol) => $rol->getValue()->value, $usersExpected[$key]->getRoles()->getValue()), $user['roles']);
        }
    }

    /**
     * @param User[] $data
     */
    private function getUsersResponseDto(array $data, array $errors, RESPONSE_STATUS $status): ResponseDto
    {
        return new ResponseDto(
            array_map(
                fn (User $user) => [
                    'id' => $user->getId()->getValue(),
                    'email' => $user->getEmail()->getValue(),
                    'password' => $user->getPassword()->getValue(),
                    'name' => $user->getName()->getValue(),
                    'roles' => array_map(fn (Rol $rol) => $rol->getvalue()->value, $user->getRoles()->getValue()),
                ],
                $data
            ),
            $errors,
            '',
            $status
        );
    }

    private function getGroupUsersDataProvider(): iterable
    {
        $usersData = $this->getUsers();

        yield [
            new GroupGetUsersDto(
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createPaginatorPage(1),
                ValueObjectFactory::createPaginatorPageItems(10),
                ValueObjectFactory::createFilter(
                    'filter_section',
                    ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP_USERS),
                    ValueObjectFactory::createNameWithSpaces('user 1 name')
                ),
                ValueObjectFactory::createFilter(
                    'text_filter',
                    ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                    ValueObjectFactory::createNameWithSpaces('user 1 name')
                ),
                true
            ),
            [$usersData[0]],
        ];

        yield [
            new GroupGetUsersDto(
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createPaginatorPage(1),
                ValueObjectFactory::createPaginatorPageItems(10),
                ValueObjectFactory::createFilter(
                    'filter_section',
                    ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP_USERS),
                    ValueObjectFactory::createNameWithSpaces('user')
                ),
                ValueObjectFactory::createFilter(
                    'text_filter',
                    ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
                    ValueObjectFactory::createNameWithSpaces('user')
                ),
                false
            ),
            array_reverse($usersData),
        ];

        yield [
            new GroupGetUsersDto(
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createPaginatorPage(1),
                ValueObjectFactory::createPaginatorPageItems(10),
                ValueObjectFactory::createFilter(
                    'filter_section',
                    ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP_USERS),
                    ValueObjectFactory::createNameWithSpaces('name')
                ),
                ValueObjectFactory::createFilter(
                    'text_filter',
                    ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
                    ValueObjectFactory::createNameWithSpaces('name')
                ),
                true
            ),
            $usersData,
        ];

        yield [
            new GroupGetUsersDto(
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createPaginatorPage(1),
                ValueObjectFactory::createPaginatorPageItems(10),
                ValueObjectFactory::createFilter(
                    'filter_section',
                    ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP_USERS),
                    ValueObjectFactory::createNameWithSpaces('1')
                ),
                ValueObjectFactory::createFilter(
                    'text_filter',
                    ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
                    ValueObjectFactory::createNameWithSpaces('1')
                ),
                true
            ),
            [$usersData[0]],
        ];

        yield [
            new GroupGetUsersDto(
                ValueObjectFactory::createIdentifier('group id'),
                ValueObjectFactory::createPaginatorPage(1),
                ValueObjectFactory::createPaginatorPageItems(10),
                null,
                null,
                true
            ),
            $usersData,
        ];
    }

    /**
     * @test
     *
     * @dataProvider getGroupUsersDataProvider
     */
    public function itShouldGetGroupUsers(GroupGetUsersDto $input, array $usersDataExpected): void
    {
        $groupUsersData = $this->getGroupUsersData();
        $usersData = $this->getUsers();
        $usersResponseDto = $this->getUsersResponseDto($usersData, [], RESPONSE_STATUS::OK);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($input->groupId)
            ->willReturn($this->pagination);

        $this->pagination
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->pagination
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupUsersData));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ModuleCommunicationConfigDto::class))
            ->willReturn($usersResponseDto);

        $return = $this->object->__invoke($input);

        $this->assertUserIsOk($usersDataExpected, $return);
    }

    /** @test */
    public function itShouldFailGetGroupUsersGroupHasNoUsers(): void
    {
        $input = new GroupGetUsersDto(
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            null,
            null,
            true
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->pagination
            ->expects($this->never())
            ->method('setPagination');

        $this->pagination
            ->expects($this->never())
            ->method('getIterator');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailGetGroupUsersErrorGettingUserData(): void
    {
        $groupUsersData = $this->getGroupUsersData();
        $input = new GroupGetUsersDto(
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            null,
            null,
            true
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($input->groupId)
            ->willReturn($this->pagination);

        $this->pagination
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->pagination
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($groupUsersData));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ModuleCommunicationConfigDto::class))
            ->willThrowException(new DomainInternalErrorException());

        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }
}
