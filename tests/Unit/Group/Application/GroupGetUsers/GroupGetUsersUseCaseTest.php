<?php

declare(strict_types=1);

namespace Test\Unit\Group\Application\GroupGetUsers;

use Common\Adapter\ModuleComumication\Exception\ModuleComunicationException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleComumication\ModuleComunicationConfigDto;
use Common\Domain\ModuleComumication\ModuleComunicationFactory;
use Common\Domain\Ports\ModuleComunication\ModuleComumunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersInputDto;
use Group\Application\GroupGetUsers\Dto\GroupGetUsersOutputDto;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersGroupNotFoundException;
use Group\Application\GroupGetUsers\Exception\GroupGetUsersUserNotInTheGroupException;
use Group\Application\GroupGetUsers\GroupGetUsersUseCase;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class GroupGetUsersUseCaseTest extends TestCase
{
    private const GROUP_ID = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';

    private GroupGetUsersUseCase $object;
    private MockObject|UserGroupRepositoryInterface $userGroupRepository;
    private MockObject|ModuleComumunicationInterface $moduleCommunication;
    private MockObject|ValidationInterface $validator;
    private MockObject|User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userGroupRepository = $this->createMock(UserGroupRepositoryInterface::class);
        $this->moduleCommunication = $this->createMock(ModuleComumunicationInterface::class);
        $this->validator = $this->createMock(ValidationInterface::class);
        $this->object = new GroupGetUsersUseCase($this->userGroupRepository, $this->moduleCommunication, $this->validator);
    }

    private function getUserSession(): User
    {
        return User::fromPrimitives('2606508b-4516-45d6-93a6-c7cb416b7f3f', 'user@emil.com', 'password', 'UserName', [USER_ROLES::USER]);
    }

    private function getUsersGroup(): array
    {
        $group = $this->createMock(Group::class);

        return [
            UserGroup::fromPrimitives(self::GROUP_ID, '1befdbe2-9c14-42f0-850f-63e061e33b8f', [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, '08eda546-739f-4ab7-917a-8a9dbee426ef', [GROUP_ROLES::USER], $group),
            UserGroup::fromPrimitives(self::GROUP_ID, '6df60afd-f7c3-4c2c-b920-e265f266c560', [GROUP_ROLES::USER], $group),
        ];
    }

    private function getUsersGroupData(): array
    {
        return [
            ['id' => '1befdbe2-9c14-42f0-850f-63e061e33b8f', 'name' => 'user1'],
            ['id' => '08eda546-739f-4ab7-917a-8a9dbee426ef', 'name' => 'user2'],
            ['id' => '6df60afd-f7c3-4c2c-b920-e265f266c560', 'name' => 'user3'],
        ];
    }

    /**
     * @param Identifier[] $userId
     */
    private function getModuleCommunicationConfigDto(array $userId): ModuleComunicationConfigDto
    {
        return ModuleComunicationFactory::userGet($userId);
    }

    /** @test */
    public function itShouldGetAllUsersOfTheGroup(): void
    {
        $userSession = $this->getUserSession();
        $usersGroup = $this->getUsersGroup();
        $usersGroupId = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId(), $usersGroup);
        $usersGroupIdPlain = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(), $usersGroup);
        $usersGroupData = $this->getUsersGroupData();
        $usersGroupNames = array_column($usersGroupData, 'name');

        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto($usersGroupId);
        $responseDtoGetUsers = new ResponseDto($usersGroupData, [], '', RESPONSE_STATUS::OK, true);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($groupId, [$userSession->getId()])
            ->willReturn([$userSession]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfigDto)
            ->willReturn($responseDtoGetUsers);

        $return = $this->object->__invoke($input);

        $this->assertInstanceOf(GroupGetUsersOutputDto::class, $return);
        $this->assertCount(count($usersGroup), $return->users);

        foreach ($return->users as $user) {
            $this->assertCount(2, $user);
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertContains($user['id'], $usersGroupIdPlain);
            $this->assertContains($user['name'], $usersGroupNames);
        }
    }

    /** @test */
    public function itShouldFailGroupHasNoUsers(): void
    {
        $userSession = $this->getUserSession();
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willThrowException(new DBNotFoundException());

        $this->userGroupRepository
            ->expects($this->never())
            ->method('findGroupUsersByUserIdOrFail');

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupGetUsersGroupNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailUserSessionIsNotInTheGroup(): void
    {
        $userSession = $this->getUserSession();
        $usersGroup = $this->getUsersGroup();

        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($groupId, [$userSession->getId()])
            ->willThrowException(new DBNotFoundException());

        $this->moduleCommunication
            ->expects($this->never())
            ->method('__invoke');

        $this->expectException(GroupGetUsersUserNotInTheGroupException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModuleCommunicationError400(): void
    {
        $userSession = $this->getUserSession();
        $usersGroup = $this->getUsersGroup();
        $usersGroupId = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId(), $usersGroup);

        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto($usersGroupId);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($groupId, [$userSession->getId()])
            ->willReturn([$userSession]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfigDto)
            ->willThrowException(new Error400Exception());

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModuleCommunicationException(): void
    {
        $userSession = $this->getUserSession();
        $usersGroup = $this->getUsersGroup();
        $usersGroupId = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId(), $usersGroup);

        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto($usersGroupId);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($groupId, [$userSession->getId()])
            ->willReturn([$userSession]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfigDto)
            ->willThrowException(new ModuleComunicationException());

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailModuleCommunicationValueError(): void
    {
        $userSession = $this->getUserSession();
        $usersGroup = $this->getUsersGroup();
        $usersGroupId = array_map(fn (UserGroup $userGroup) => $userGroup->getUserId(), $usersGroup);

        $moduleCommunicationConfigDto = $this->getModuleCommunicationConfigDto($usersGroupId);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $limit = 50;
        $offset = 0;
        $input = new GroupGetUsersInputDto($userSession, self::GROUP_ID, $limit, $offset);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersOrFail')
            ->with($groupId, $limit, $offset)
            ->willReturn($usersGroup);

        $this->userGroupRepository
            ->expects($this->once())
            ->method('findGroupUsersByUserIdOrFail')
            ->with($groupId, [$userSession->getId()])
            ->willReturn([$userSession]);

        $this->moduleCommunication
            ->expects($this->once())
            ->method('__invoke')
            ->with($moduleCommunicationConfigDto)
            ->willThrowException(new \ValueError());

        $this->expectException(DomainErrorException::class);
        $this->object->__invoke($input);
    }
}
