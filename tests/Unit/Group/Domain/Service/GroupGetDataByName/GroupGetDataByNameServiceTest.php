<?php

declare(strict_types=1);

namespace Test\Unit\Group\Domain\Service\GroupGetDataByName;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupGetDataByName\Dto\GroupGetDataByNameDto;
use Group\Domain\Service\GroupGetDataByName\GroupGetDataByNameService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetDataByNameServiceTest extends TestCase
{
    private const APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const GROUP_PUBLIC_PATH = 'group/public/path';

    private GroupGetDataByNameService $object;
    private MockObject|GroupRepositoryInterface $groupRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupRepository = $this->createMock(GroupRepositoryInterface::class);
        $this->object = new GroupGetDataByNameService($this->groupRepository, self::GROUP_PUBLIC_PATH, self::APP_PROTOCOL_AND_DOMAIN);
    }

    private function getGroupData(): Group
    {
        return Group::fromPrimitives(
            '0ed6999b-11b5-4a20-8e37-38f8d623caa7',
            'groupForTesting',
            GROUP_TYPE::GROUP,
            'Description of the group',
            'path/to/image'
        );
    }

    private function assertGroupIdOk(Group $groupDataExpected, array $groupDataActual): void
    {
        $this->assertArrayHasKey('group_id', $groupDataActual);
        $this->assertArrayHasKey('name', $groupDataActual);
        $this->assertArrayHasKey('description', $groupDataActual);
        $this->assertArrayHasKey('image', $groupDataActual);
        $this->assertArrayHasKey('created_on', $groupDataActual);

        $this->assertEquals($groupDataExpected->getId()->getValue(), $groupDataActual['group_id']);
        $this->assertEquals($groupDataExpected->getName()->getValue(), $groupDataActual['name']);
        $this->assertEquals($groupDataExpected->getDescription()->getValue(), $groupDataActual['description']);

        if (null === $groupDataExpected->getImage()->getValue()) {
            $this->assertEquals(null, $groupDataActual['image']);
        } else {
            $this->assertEquals(self::APP_PROTOCOL_AND_DOMAIN.self::GROUP_PUBLIC_PATH.'/'.$groupDataExpected->getImage()->getValue(), $groupDataActual['image']);
        }
    }

    /** @test */
    public function itShouldGetTheGroupDataByTheName(): void
    {
        $groupDataExpected = $this->getGroupData();
        $input = new GroupGetDataByNameDto(
            ValueObjectFactory::createNameWithSpaces('groupOne')
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupByNameOrFail')
            ->with($input->groupName)
            ->willReturn($groupDataExpected);

        $return = $this->object->__invoke($input);

        $this->assertGroupIdOk($groupDataExpected, $return);
    }

    /** @test */
    public function itShouldGetTheGroupDataByTheNameNoImage(): void
    {
        $groupDataExpected = $this->getGroupData();
        $groupDataExpected->setImage(ValueObjectFactory::createPath(null));
        $input = new GroupGetDataByNameDto(
            ValueObjectFactory::createNameWithSpaces('groupOne')
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupByNameOrFail')
            ->with($input->groupName)
            ->willReturn($groupDataExpected);

        $return = $this->object->__invoke($input);

        $this->assertGroupIdOk($groupDataExpected, $return);
    }

    /** @test */
    public function itShouldFailGettingTheGroupDataByTheName(): void
    {
        $input = new GroupGetDataByNameDto(
            ValueObjectFactory::createNameWithSpaces('groupOne')
        );

        $this->groupRepository
            ->expects($this->once())
            ->method('findGroupByNameOrFail')
            ->with($input->groupName)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
