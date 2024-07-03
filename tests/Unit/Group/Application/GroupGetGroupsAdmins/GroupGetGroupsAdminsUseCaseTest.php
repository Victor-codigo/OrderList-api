<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetGroupsAdmins;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsInputDto;
use Group\Application\GroupGetGroupsAdmins\Dto\GroupGetGroupsAdminsOutputDto;
use Group\Application\GroupGetGroupsAdmins\Exception\GroupGetGroupsAdminsNotFoundException;
use Group\Application\GroupGetGroupsAdmins\GroupGetGroupsAdminsUseCase;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupGetGroupsAdminsUseCaseTest extends TestCase
{
    private const array GROUPS_ID = [
        'd517807e-3494-4d8d-b0c7-ee80dfd1c01c',
        '0036f2df-53cf-44fb-ac4f-5948ecc0f351',
        '68ac6040-ec8f-4777-b17c-e0f6b030be2a',
    ];

    private GroupGetGroupsAdminsUseCase $object;
    private MockObject|ValidationInterface $validator;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|UserShared $userSession;
    private MockObject|PaginatorInterface $userGroupPaginator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = $this->createMock(ValidationInterface::class);
        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->userSession = $this->createMock(UserShared::class);
        $this->userGroupPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new GroupGetGroupsAdminsUseCase(
            $this->validator,
            $this->userGroupRepository
        );
    }

    /**
     * @return UserGroup[]
     */
    private function getUsersGroups(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(
                'group id 1',
                'user id 1',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group id 1',
                'user id 2',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group id 1',
                'user id 3',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group id 2',
                'user id 3',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group id 3',
                'user id 4',
                [GROUP_ROLES::ADMIN],
                $group
            ),
            UserGroup::fromPrimitives(
                'group id 3',
                'user id 5',
                [GROUP_ROLES::ADMIN],
                $group
            ),
        ];
    }

    /**
     * @return UserGroup[]
     */
    private function getUsersGroupsExpected(): GroupGetGroupsAdminsOutputDto
    {
        return new GroupGetGroupsAdminsOutputDto([
            'group id 1' => [
                'user id 1',
                'user id 2',
                'user id 3',
            ],
            'group id 2' => [
                'user id 3',
            ],
            'group id 3' => [
                'user id 4',
                'user id 5',
            ],
        ],
            ValueObjectFactory::createPaginatorPage(1),
            100
        );
    }

    /** @test */
    public function itShouldGetGroupsAdmins(): void
    {
        $userGroups = $this->getUsersGroups();
        $userGroupsIdExpected = $this->getUsersGroupsExpected();
        $input = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            self::GROUPS_ID,
            1,
            100
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersOrFail')
            ->with($input->groupsId)
            ->willReturn($this->userGroupPaginator);

        $this->userGroupPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 100);

        $this->userGroupPaginator
            ->expects($this->once())
            ->method('getPagesTotal')
            ->willReturn(100);

        $this->userGroupPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($userGroups));

        $return = $this->object->__invoke($input);

        $this->assertEquals($userGroupsIdExpected, $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $input = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            ['wrong id'],
            1,
            100
        );

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupsUsersOrFail');

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('getPagesTotal');

        $this->validator
            ->expects($this->any())
            ->method('validateValueObjectArray')
            ->willReturn(['groups_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]);

        $this->expectException(ValueObjectValidationException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailNoGroupsFound(): void
    {
        $input = new GroupGetGroupsAdminsInputDto(
            $this->userSession,
            self::GROUPS_ID,
            1,
            100
        );

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupsUsersOrFail')
            ->with($input->groupsId)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('getPagesTotal');

        $this->userGroupPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(GroupGetGroupsAdminsNotFoundException::class);
        $this->object->__invoke($input);
    }
}
