<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupGetUsers;

use Override;
use ArrayIterator;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
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
    private const string APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const string USER_PUBLIC_IMAGE_PATH = '/userPublicImagePath';

    private GroupGetUsersService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|ModuleCommunicationInterface $moduleCommunication;
    private MockObject|PaginatorInterface $pagination;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleCommunicationInterface::class);
        $this->pagination = $this->createMock(PaginatorInterface::class);
        $this->object = new GroupGetUsersService(
            $this->userGroupRepository,
            $this->moduleCommunication,
            self::USER_PUBLIC_IMAGE_PATH,
            self::APP_PROTOCOL_AND_DOMAIN,
        );
    }

    private function getUsers(): array
    {
        return [
            [
                'id' => 'user 1 id',
                'name' => 'user 1 name',
                'image' => 'user 1 image',
                'created_on' => 'user 1 created on',
                'admin' => false,
            ],
            [
                'id' => 'user 2 id',
                'name' => 'user 2 name',
                'image' => 'user 2 image',
                'created_on' => 'user 2 created on',
                'admin' => true,
            ],
            [
                'id' => 'user 3 id',
                'name' => 'user 3 name',
                'image' => null,
                'created_on' => 'user 3 created on',
                'admin' => false,
            ],
        ];
    }

    private function getUsersExpected(): array
    {
        $users = $this->getUsers();

        return array_map(
            function (array $user) {
                if (null !== $user['image']) {
                    $user['image'] = self::APP_PROTOCOL_AND_DOMAIN.self::USER_PUBLIC_IMAGE_PATH.'/'.$user['image'];
                }

                return $user;
            },
            $users
        );
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
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('image', $user);
            $this->assertArrayHasKey('created_on', $user);
            $this->assertArrayHasKey('admin', $user);

            $this->assertEquals($usersExpected[$key]['id'], $user['id']);
            $this->assertEquals($usersExpected[$key]['name'], $user['name']);
            $this->assertEquals($usersExpected[$key]['image'], $user['image']);
            $this->assertEquals($usersExpected[$key]['created_on'], $user['created_on']);
            $this->assertIsBool($user['admin']);
        }
    }

    /**
     * @param User[] $data
     */
    private function getUsersResponseDto(array $data, array $errors, RESPONSE_STATUS $status): ResponseDto
    {
        return new ResponseDto(
            array_map(
                fn (array $user): array => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'image' => null === $user['image']
                        ? null
                        : self::APP_PROTOCOL_AND_DOMAIN.self::USER_PUBLIC_IMAGE_PATH.'/'.$user['image'],
                    'created_on' => $user['created_on'],
                    'admin' => $user['admin'],
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
        $usersExpectedData = $this->getUsersExpected();

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
            [$usersExpectedData[0]],
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
            array_reverse($usersExpectedData),
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
            $usersExpectedData,
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
            [$usersExpectedData[0]],
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
            $usersExpectedData,
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
            ->willReturn(new ArrayIterator($groupUsersData));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ModuleCommunicationConfigDto::class))
            ->willReturn($usersResponseDto);

        $return = $this->object->__invoke($input);

        $this->assertUserIsOk($usersDataExpected, $return);
    }

    /** @test */
    public function itShouldFailGettingGroupUsersUserNameNotFound(): void
    {
        $input = new GroupGetUsersDto(
            ValueObjectFactory::createIdentifier('group id'),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            ValueObjectFactory::createFilter(
                'filter_section',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP_USERS),
                ValueObjectFactory::createNameWithSpaces('user not exists')
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                ValueObjectFactory::createNameWithSpaces('user not exists')
            ),
            true
        );
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
            ->willReturn(new ArrayIterator($groupUsersData));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ModuleCommunicationConfigDto::class))
            ->willReturn($usersResponseDto);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
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
            ->willReturn(new ArrayIterator($groupUsersData));

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->isInstanceOf(ModuleCommunicationConfigDto::class))
            ->willThrowException(new DomainInternalErrorException());

        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }
}
