<?php

declare(strict_types=1);

namespace Test\Unit\User\Domain\Service\GetUsersPublicData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPablicDataOutputDto;
use User\Domain\Service\GetUsersPublicData\Dto\GetUsersPublicDataDto;
use User\Domain\Service\GetUsersPublicData\GeUsersPublicDataService;

class GetUsersPublcDataServiceTest extends TestCase
{
    private GeUsersPublicDataService $object;
    private MockObject|UserRepositoryInterface $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->object = new GeUsersPublicDataService($this->userRepository);
    }

    private function getUsersId(): array
    {
        return [
            ValueObjectFactory::createIdentifier('0b13e52d-b058-32fb-8507-10dec634a07c'),
            ValueObjectFactory::createIdentifier('0b17ca3e-490b-3ddb-aa78-35b4ce668dc0'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8f'),
        ];
    }

    private function getUsers(): array
    {
        return [
            User::fromPrimitives('0b13e52d-b058-32fb-8507-10dec634a07c', 'email@domain.com', 'password1', 'name1', [USER_ROLES::USER]),
            User::fromPrimitives('0b17ca3e-490b-3ddb-aa78-35b4ce668dc0', 'email@domain.com', 'password1', 'name1', [USER_ROLES::USER]),
            User::fromPrimitives('1befdbe2-9c14-42f0-850f-63e061e33b8f', 'email@domain.com', 'password1', 'name1', [USER_ROLES::USER]),
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
        $return = $this->object->__invoke($usersDto);

        $this->assertInstanceOf(GetUsersPablicDataOutputDto::class, $return);

        foreach ($return->usersData as $user) {
            $this->assertArrayHasKey('id', $user);
            $this->assertArrayHasKey('name', $user);
            $this->assertContains($user['id'], $expectedUsersIdentifiers);
            $this->assertContains($user['name'], $expectedUsersNames);
        }
    }

    /** @test */
    public function itShouldFailNoUsersFound(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->userRepository
            ->expects($this->once())
            ->method('findUsersByIdOrFail')
            ->with([])
            ->willThrowException(DBNotFoundException::fromMessage(''));

        $usersDto = new GetUsersPublicDataDto([]);
        $this->object->__invoke($usersDto);
    }
}
