<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetDataServiceTest extends TestCase
{
    private const string PATH_APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const string PATH_GROUP_IMAGES_PUBLIC = '/assets/img/groups';

    private GroupGetDataService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupGetDataService($this->groupRepository, self::PATH_GROUP_IMAGES_PUBLIC, self::PATH_APP_PROTOCOL_AND_DOMAIN);
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
            Group::fromPrimitives('a5002966-dbf7-4f76-a862-23a04b5ca465', 'GroupTwo', GROUP_TYPE::USER, 'This is a group of one user', 'image.file'),
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

    #[Test]
    public function itShouldGetTheDataFromTheGroupsTypeGroup(): void
    {
        $expectedGroupsData = $this->getGroupsTypeGroupData();
        $groupsId = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $expectedGroupsData
        );
        $userImage = ValueObjectFactory::createPath('image.file');
        $input = new GroupGetDataDto($groupsId, GROUP_TYPE::GROUP, $userImage);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        /**
         * @var array<int, array{
         *  group_id: string|null,
         *  type:string,
         *  name:string|null,
         *  description:string|null,
         *  image: string|null,
         *  created_on: string
         * }> $return
         */
        $return = iterator_to_array($this->object->__invoke($input));

        $this->assertCount(2, $return);
        foreach ($return as $key => $groupData) {
            $this->assertArrayHasKey('group_id', $groupData);
            $this->assertArrayHasKey('type', $groupData);
            $this->assertArrayHasKey('name', $groupData);
            $this->assertArrayHasKey('description', $groupData);
            $this->assertArrayHasKey('image', $groupData);
            $this->assertArrayHasKey('created_on', $groupData);
            $this->assertEquals($expectedGroupsData[$key]->getId()->getValue(), $groupData['group_id']);
            $this->assertEquals(GROUP_TYPE::GROUP === $expectedGroupsData[$key]->getType()->getValue() ? 'group' : 'user', $groupData['type']);
            $this->assertEquals($expectedGroupsData[$key]->getName()->getValue(), $groupData['name']);
            $this->assertEquals($expectedGroupsData[$key]->getDescription()->getValue(), $groupData['description']);
            $this->assertEquals($expectedGroupsData[$key]->getCreatedOn()->format('Y-m-d H:i:s'), $groupData['created_on']);

            if (null === $expectedGroupsData[$key]->getImage()->getValue()) {
                $this->assertNull($groupData['image']);
            } else {
                $this->assertEquals(
                    self::PATH_APP_PROTOCOL_AND_DOMAIN.self::PATH_GROUP_IMAGES_PUBLIC."/{$expectedGroupsData[$key]->getImage()->getValue()}",
                    $groupData['image']
                );
            }
        }
    }

    #[Test]
    public function itShouldGetTheDataFromTheGroupsTypeUser(): void
    {
        $expectedGroupsData = $this->getGroupsTypeUserData();
        $groupsId = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $expectedGroupsData
        );
        $userImage = ValueObjectFactory::createPath('image.file');
        $input = new GroupGetDataDto($groupsId, GROUP_TYPE::USER, $userImage);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        /**
         * @var array<int, array{
         *  group_id: string|null,
         *  type: string,
         *  name: string|null,
         *  description: string|null,
         *  image: string|null,
         *  created_on: string
         * }> $return
         */
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
                    $userImage->getValue(),
                    $groupData['image']
                );
            }
        }
    }

    #[Test]
    public function itShouldGetTheDataFromTheGroupsTypeUndefined(): void
    {
        $expectedGroupsData = $this->getGroupsTypeUndefinedData();
        $groupsId = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $expectedGroupsData
        );
        $userImage = ValueObjectFactory::createPath('image.file');
        $input = new GroupGetDataDto($groupsId, null, $userImage);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        /**
         * @var array<int, array{
         *  group_id: string|null,
         *  type: string,
         *  name: string|null,
         *  description: string|null,
         *  image: string|null,
         *  created_on: string
         * }> $return
         */
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
            } elseif (GROUP_TYPE::USER === $expectedGroupsData[$key]->getType()->getValue()) {
                $this->assertEquals(
                    $userImage->getValue(),
                    $groupData['image']
                );
            } else {
                $this->assertEquals(
                    self::PATH_APP_PROTOCOL_AND_DOMAIN.self::PATH_GROUP_IMAGES_PUBLIC."/{$expectedGroupsData[$key]->getImage()->getValue()}",
                    $groupData['image']
                );
            }
        }
    }

    #[Test]
    public function itShouldFailNoGroupsFound(): void
    {
        $expectedGroupsData = $this->getGroupsTypeGroupData();
        $groupsId = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $expectedGroupsData
        );
        $userImage = ValueObjectFactory::createPath('image.file');
        $input = new GroupGetDataDto($groupsId, null, $userImage);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailNoGroupsOfTypeUserFound(): void
    {
        $expectedGroupsData = $this->getGroupsTypeGroupData();
        $groupsId = array_map(
            fn (Group $group): Identifier => $group->getId(),
            $expectedGroupsData
        );
        $userImage = ValueObjectFactory::createPath('image.file');
        $input = new GroupGetDataDto($groupsId, GROUP_TYPE::USER, $userImage);

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupsByIdOrFail')
            ->with($input->groupsId)
            ->willReturn($expectedGroupsData);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
