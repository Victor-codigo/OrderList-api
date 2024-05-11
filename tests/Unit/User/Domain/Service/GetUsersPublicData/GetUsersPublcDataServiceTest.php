<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\GetUsersPublicData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Struct\SCOPE;
use Common\Domain\Validation\User\USER_ROLES;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataOutputDto;
use User\Domain\Service\GetUsersPublicData\GeUsersPublicDataService;

class GetUsersPublcDataServiceTest extends TestCase
{
    private const USER_PUBLIC_IMAGE_PATH = 'userPublicImagePath';
    private const APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';

    private GeUsersPublicDataService $object;
    private MockObject|UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->object = new GeUsersPublicDataService($this->userRepository, self::USER_PUBLIC_IMAGE_PATH, self::APP_PROTOCOL_AND_DOMAIN);
    }

    private function getUsersId(): array
    {
        return [
            ValueObjectFactory::createIdentifier('0b13e52d-b058-32fb-8507-10dec634a07c'),
            ValueObjectFactory::createIdentifier('0b17ca3e-490b-3ddb-aa78-35b4ce668dc0'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8f'),
        ];
    }

    private function getUsersIdDeletedOrNotActive(): array
    {
        return [
            ValueObjectFactory::createIdentifier('42610729-5cd9-35f3-86bc-ec9158e1797e'),   // not active
            ValueObjectFactory::createIdentifier('68e94495-16f0-4acd-adbe-f2b9575e6544'),   // deleted
        ];
    }

    private function getUsers(): array
    {
        return [
            User::fromPrimitives('0b13e52d-b058-32fb-8507-10dec634a07c', 'email@domain.com', 'password1', 'name1', [USER_ROLES::USER]),
            User::fromPrimitives('0b17ca3e-490b-3ddb-aa78-35b4ce668dc0', 'email2@domain.com', 'password2', 'name2', [USER_ROLES::USER]),
            User::fromPrimitives('1befdbe2-9c14-42f0-850f-63e061e33b8f', 'email3@domain.com', 'password3', 'name3', [USER_ROLES::ADMIN]),
        ];
    }

    private function getUsersDeletedOrNotActive(): array
    {
        return [
            User::fromPrimitives('42610729-5cd9-35f3-86bc-ec9158e1797e', 'email4@domain.com', 'password1', 'name1', [USER_ROLES::NOT_ACTIVE]), // not active
            User::fromPrimitives('68e94495-16f0-4acd-adbe-f2b9575e6544', 'email5@domain.com', 'password2', 'name2', [USER_ROLES::DELETED]),    // deleted
        ];
    }

    /** @test */
    public function itShouldGetTheUsersPublicData(): void
    {
        $usersId = $this->getUsersId();
        $expectedUsers = $this->getUsers();
        $expectedUsersNames = array_map(fn (User $user) => $user->getName(), $expectedUsers);
        $expectedUsersIdentifiers = array_map(fn (User $user) => $user->getId(), $expectedUsers);

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByIdOrFail')
            ->with($usersId)
            ->willReturn($expectedUsers);

        $usersDto = new GetUsersPublicDataDto($usersId);
        $return = $this->object->__invoke($usersDto, SCOPE::PUBLIC);

        $this->assertInstanceOf(GetUsersPublicDataOutputDto::class, $return);

        foreach ($return->usersData as $user) {
            $this->assertCount(2, $user);
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertContains($user['id'], $expectedUsersIdentifiers);
            $this->assertContains($user['name'], $expectedUsersNames);
        }
    }

    /** @test */
    public function itShouldGetTheUsersPrivateData(): void
    {
        $usersId = $this->getUsersId();
        $expectedUsers = $this->getUsers();
        $expectedUsersIdentifiers = array_map(fn (User $user) => $user->getId(), $expectedUsers);
        $expectedUsersEmail = array_map(fn (User $user) => $user->getEmail(), $expectedUsers);
        $expectedUsersNames = array_map(fn (User $user) => $user->getName(), $expectedUsers);
        $expectedUsersRoles = array_map(fn (User $user) => $user->getRoles(), $expectedUsers);
        $expectedUsersImages = array_map(fn (User $user) => $user->getImage(), $expectedUsers);
        $expectedUsersCreatedOn = array_map(fn (User $user) => $user->getCreatedOn(), $expectedUsers);

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByIdOrFail')
            ->with($usersId)
            ->willReturn($expectedUsers);

        $usersDto = new GetUsersPublicDataDto($usersId);
        $return = $this->object->__invoke($usersDto, SCOPE::PRIVATE);

        $this->assertInstanceOf(GetUsersPublicDataOutputDto::class, $return);

        foreach ($return->usersData as $user) {
            $this->assertCount(6, $user);
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('email', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertArrayHasKey('roles', $user);
            $this->assertArrayHasKey('image', $user);
            $this->assertArrayHasKey('created_on', $user);

            $this->assertContains($user['id'], $expectedUsersIdentifiers);
            $this->assertContains($user['email'], $expectedUsersEmail);
            $this->assertContains($user['name'], $expectedUsersNames);
            $this->assertContainsEquals($user['roles'], $expectedUsersRoles);
            $this->assertContainsEquals($user['image'], $expectedUsersImages);
            $this->assertContains($user['created_on'], $expectedUsersCreatedOn);
        }
    }

    /** @test */
    public function itShouldGetOnlyUsersThatAreActiveAndNotDeleted(): void
    {
        $usersId = array_merge($this->getUsersId(), $this->getUsersIdDeletedOrNotActive());
        $expectedUsers = array_merge($this->getUsers(), $this->getUsersDeletedOrNotActive());

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByIdOrFail')
            ->with($usersId)
            ->willReturn($expectedUsers);

        $usersDto = new GetUsersPublicDataDto($usersId);
        $return = $this->object->__invoke($usersDto, SCOPE::PUBLIC);

        $this->assertInstanceOf(GetUsersPublicDataOutputDto::class, $return);
        $this->assertCount(count($this->getUsersId()), $return->usersData);
    }

    /** @test */
    public function itShouldGetUsersByName(): void
    {
        $usersName = array_map(fn (User $user) => $user->getName(), $this->getUsers());
        $expectedUsers = $this->getUsers();

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByNameOrFail')
            ->with($usersName)
            ->willReturn($expectedUsers);

        $usersDto = new GetUsersPublicDataDto($usersName);
        $return = $this->object->__invoke($usersDto, SCOPE::PUBLIC);

        $this->assertInstanceOf(GetUsersPublicDataOutputDto::class, $return);
        $this->assertCount(count($usersName), $return->usersData);
    }

    /** @test */
    public function itShouldFailNoUsersPassToFind(): void
    {
        $this->expectException(LogicException::class);

        $usersDto = new GetUsersPublicDataDto([]);
        $this->object->__invoke($usersDto, SCOPE::PRIVATE);
    }

    /** @test */
    public function itShouldFailUsersPassAreNotIdentifiersOrNames(): void
    {
        $users = [
            'user1',
            'user2',
            'user3',
        ];

        $this->expectException(LogicException::class);

        $usersDto = new GetUsersPublicDataDto($users);
        $this->object->__invoke($usersDto, SCOPE::PRIVATE);
    }

    /** @test */
    public function itShouldFailNoUsersFound(): void
    {
        $usersId = array_map(fn (User $user) => $user->getId(), $this->getUsersDeletedOrNotActive());

        $this->userRepository
        ->expects($this->once())
        ->method('findUsersByIdOrFail')
        ->with($usersId)
        ->willThrowException(DBNotFoundException::fromMessage(''));

        $this->expectException(DBNotFoundException::class);
        $usersDto = new GetUsersPublicDataDto($usersId);
        $this->object->__invoke($usersDto, SCOPE::PRIVATE);
    }

    /** @test */
    public function itShouldFailUsersAreNotValid(): void
    {
        $usersId = $this->getUsersIdDeletedOrNotActive();
        $expectedUsers = $this->getUsersDeletedOrNotActive();

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByIdOrFail')
            ->with($usersId)
            ->willReturn($expectedUsers);

        $usersDto = new GetUsersPublicDataDto($usersId);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($usersDto, SCOPE::PRIVATE);
    }
}
