<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetDataServiceTest extends TestCase
{
    private const PATH_GROUP_IMAGES_PUBLIC = 'assets/img/groups';

    private GroupGetDataService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupGetDataService($this->groupRepository, self::PATH_GROUP_IMAGES_PUBLIC);
    }

    /**
     * @return Group[]
     */
    private function getGroupsTypeGroupData(): array
    {
        return [
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'GroupOneName', GROUP_TYPE::GROUP, 'group one description', null),
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'GroupOneName', GROUP_TYPE::GROUP, 'group one description', 'image.png'),
        ];
    }

    /**
     * @return Group[]
     */
    private function getGroupsTypeUserData(): array
    {
        return [
            Group::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user', null),
        ];
    }

    /**
     * @return Group[]
     */
    private function getGroupsTypeUndefinedData(): array
    {
        return [
            Group::fromPrimitives('4b513296-14ac-4fb1-a574-05bc9b1dbe3f', 'GroupOneName', GROUP_TYPE::GROUP, 'group one description', null),
            Group::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user', 'image.png'),
            Group::fromPrimitives('fdb242b4-bac8-4463-88d0-0941bb0beee0', 'GroupOneName', GROUP_TYPE::GROUP, 'group one description', null),
        ];
    }

    /** @test */
    public function itShouldGetTheDataFromTheGroupsTypeGroup(): void
    {
        $expectedGroupsData = $this->getGroupsTypeGroupData();
        $groupsId = array_map(
            fn (Group $group) => $group->getId(),
            $expectedGroupsData
        );
        $input = new GroupGetDataDto($groupsId, GROUP_TYPE::GROUP);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        $return = iterator_to_array($this->object->__invoke($input));

        $this->assertCount(2, $return);
        foreach ($return as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('image', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);
            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);

            if (null === $expectedGroupsData[$key]->getImage()->getValue()) {
                $this->assertNull($groupData['image']);
            } else {
                $this->assertEquals(
                    self::PATH_GROUP_IMAGES_PUBLIC."/{$expectedGroupsData[$key]->getImage()->getValue()}",
                    $groupData['image']
                );
            }
        }
    }

    /** @test */
    public function itShouldGetTheDataFromTheGroupsTypeUser(): void
    {
        $expectedGroupsData = $this->getGroupsTypeUserData();
        $groupsId = array_map(
            fn (Group $group) => $group->getId(),
            $expectedGroupsData
        );
        $input = new GroupGetDataDto($groupsId, GROUP_TYPE::USER);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        $return = iterator_to_array($this->object->__invoke($input));

        $this->assertCount(1, $return);
        foreach ($return as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('image', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);
            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);

            if (null === $expectedGroupsData[$key]->getImage()->getValue()) {
                $this->assertNull($groupData['image']);
            } else {
                $this->assertEquals(
                    self::PATH_GROUP_IMAGES_PUBLIC."/{$expectedGroupsData[$key]->getImage()->getValue()}",
                    $groupData['image']
                );
            }
        }
    }

    /** @test */
    public function itShouldGetTheDataFromTheGroupsTypeUndefined(): void
    {
        $expectedGroupsData = $this->getGroupsTypeUndefinedData();
        $groupsId = array_map(
            fn (Group $group) => $group->getId(),
            $expectedGroupsData
        );
        $input = new GroupGetDataDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        $return = iterator_to_array($this->object->__invoke($input));

        $this->assertCount(3, $return);
        foreach ($return as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('image', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);
            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);

            if (null === $expectedGroupsData[$key]->getImage()->getValue()) {
                $this->assertNull($groupData['image']);
            } else {
                $this->assertEquals(
                    self::PATH_GROUP_IMAGES_PUBLIC."/{$expectedGroupsData[$key]->getImage()->getValue()}",
                    $groupData['image']
                );
            }
        }
    }

    /** @test */
    public function itShouldFailNoGroupsFound(): void
    {
        $expectedGroupsData = $this->getGroupsTypeGroupData();
        $groupsId = array_map(
            fn (Group $group) => $group->getId(),
            $expectedGroupsData
        );
        $input = new GroupGetDataDto($groupsId);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
