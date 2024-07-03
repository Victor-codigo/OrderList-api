<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupUserGetGroups;

use Override;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsDto;
use Group\Domain\Service\GroupUserGetGroups\GroupUserGetGroupsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;

class GroupUserGetGroupsServiceTest extends TestCase
{
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const string PATH_TO_GROUP_IMAGE_PUBLIC_PATH = '/groupPublicImagePath';

    private GroupUserGetGroupsService $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|GroupRepositoryInterface $groupRepository;
    private GroupGetDataService $groupGetDataService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);

        $this->groupGetDataService = new GroupGetDataService($this->groupRepository, self::PATH_TO_GROUP_IMAGE_PUBLIC_PATH, self::APP_PROTOCOL_AND_DOMAIN);
        $this->object = new GroupUserGetGroupsService($this->userGroupRepository, $this->groupGetDataService);
    }

    private function getUserId(): Identifier
    {
        return ValueObjectFactory::createIdentifier(self::USER_ID);
    }

    /**
     * @return UserGroup[]
     */
    private function getUserGroups(): MockObject|PaginatorInterface
    {
        $group = $this->createMock(Group::class);
        $userGroups = [
            UserGroup::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', self::USER_ID, [GROUP_ROLES::ADMIN], $group),
            UserGroup::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', self::USER_ID, [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', self::USER_ID, [GROUP_ROLES::ADMIN], $group),
        ];

        /** @var MockObject|PaginatorInterface $paginator */
        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getIterator')
            ->willReturnCallback(function () use ($userGroups) {
                foreach ($userGroups as $group) {
                    yield $group;
                }
            });

        return $paginator;
    }

    /**
     * @return Group[]
     */
    private function getGroupsData(): array
    {
        return
        [
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'Group100Users', GROUP_TYPE::GROUP, 'This group contains 100 users', '2023-02-23 17:18:03'),
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'GroupOne', GROUP_TYPE::GROUP, 'This is a group of users', '2023-02-23 17:18:03'),
        ];
    }

    private function mockGroupGetDataService(array $groupsId, array $expectedGroupsData, InvocationOrder $timesInvokedFindGroupsByIdOrFail): void
    {
        $this->groupRepository
            ->expects($timesInvokedFindGroupsByIdOrFail)
            ->method('findGroupsByIdOrFail')
            ->with($groupsId)
            ->willReturn($expectedGroupsData);
    }

    /** @test */
    public function itShouldGetUserGroupsAllData(): void
    {
        $expectedUserGroups = $this->getUserGroups();
        $expectedGroupsData = $this->getGroupsData();
        $groupGetDataDto = new GroupGetDataDto(
            array_map(
                fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
                iterator_to_array($expectedUserGroups)
            ),
            null,
            ValueObjectFactory::createPath('image.file')
        );

        $input = new GroupUserGetGroupsDto(
            $this->getUserId(),
            $groupGetDataDto->userImage,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(100),
            null,
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP),
                ValueObjectFactory::createNameWithSpaces('groupName')
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
                ValueObjectFactory::createNameWithSpaces('groupName')
            ),
            true
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsByName')
            ->with($input->userId, $input->filterText, $input->groupType, $input->orderAsc)
            ->willReturn($expectedUserGroups);

        $this->mockGroupGetDataService($groupGetDataDto->groupsId, $expectedGroupsData, $this->once());

        $return = $this->object->__invoke($input);

        $this->assertTrue(property_exists($return, 'page'));
        $this->assertTrue(property_exists($return, 'pagesTotal'));
        $this->assertTrue(property_exists($return, 'groups'));
        $this->assertCount(count($expectedGroupsData), $return->groups);

        foreach ($return->groups as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('type', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);
            $this->assertArrayHasKey('admin', $groupData);

            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals($expectedGroupsData[$key]->getType()->getValue()->value === GROUP_TYPE::GROUP->value ? 'group' : 'user', $groupData['type']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);
            $this->assertTrue($groupData['admin']);
        }
    }

    /** @test */
    public function itShouldFailNoGroupsFound(): void
    {
        $userId = $this->getUserId();
        $paginatorPage = ValueObjectFactory::createPaginatorPage(1);
        $paginatorPageItems = ValueObjectFactory::createPaginatorPageItems(100);
        $expectedUserGroups = $this->getUserGroups();
        $expectedGroupsData = $this->getGroupsData();
        $groupGetDataDto = new GroupGetDataDto(
            array_map(
                fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
                iterator_to_array($expectedUserGroups)
            ),
            null,
            ValueObjectFactory::createPath('image.file')
        );
        $input = new GroupUserGetGroupsDto(
            $userId,
            $groupGetDataDto->userImage,
            $paginatorPage,
            $paginatorPageItems,
            null,
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::GROUP),
                ValueObjectFactory::createNameWithSpaces('groupName')
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
                ValueObjectFactory::createNameWithSpaces('groupName')
            ),
            true
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findUserGroupsByName')
            ->with($userId)
            ->willThrowException(new DBNotFoundException());

        $this->mockGroupGetDataService($groupGetDataDto->groupsId, $expectedGroupsData, $this->never());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
