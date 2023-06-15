<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;
use User\Adapter\Database\Orm\Doctrine\Repository\UserRepository;
use User\Domain\Model\User;

class UserRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const USER_ID = '1befdbe2-9c14-42f0-850f-63e061e33b8f';
    private const USER_EMAIL = 'email.already.exists@host.com';
    private const USER_NOT_ACTIVE_ID = 'bd2cbad1-6ccf-48e3-bb92-bc9961bc011e';

    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->entityManager->getRepository(User::class);
    }

    /** @test */
    public function itShouldSaveTheUserInDataBase(): void
    {
        $userNew = $this->getNewUser();
        $this->userRepository->save($userNew);
        $userSaved = $this->userRepository->findOneBy(['id' => $userNew->getId()]);

        $this->assertSame($userNew, $userSaved);
    }

    /** @test */
    public function itShouldFailEmailAlreadyExists()
    {
        $this->expectException(DBUniqueConstraintException::class);

        $this->userRepository->save($this->getExitsUser());
    }

    /** @test */
    public function itShouldFailSavingDataBaseError(): void
    {
        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->userRepository, $objectManagerMock);
        $this->userRepository->save($this->getNewUser());
    }

    /** @test */
    public function itShouldRemoveTheUserInDataBase(): void
    {
        $userNew = $this->getNewUser();
        $this->userRepository->remove([$userNew]);
        $userRemoved = $this->userRepository->findOneBy(['id' => $userNew->getId()]);

        $this->assertEmpty($userRemoved);
    }

    /** @test */
    public function itShouldFailRemovingDataBaseError(): void
    {
        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->userRepository, $objectManagerMock);
        $this->userRepository->remove([$this->getNewUser()]);
    }

    /** @test */
    public function itShouldFindAUserById(): void
    {
        $userId = new Identifier(self::USER_ID);
        $return = $this->userRepository->findUserByIdOrFail($userId);

        $this->assertEquals($userId, $return->getId());
    }

    /** @test */
    public function itShouldFailFindingAUserById(): void
    {
        $this->expectException(DBNotFoundException::class);

        $userId = new Identifier(self::USER_ID.'-Not valid id');
        $this->userRepository->findUserByIdOrFail($userId);
    }

    /** @test */
    public function itShouldFindAUserByIdNoCache(): void
    {
        $userId = new Identifier(self::USER_ID);
        $return = $this->userRepository->findUserByIdOrFail($userId);
        $return->setEmail(ValueObjectFactory::createEmail('other.email@host.com'));
        $returnNoCache = $this->userRepository->findUserByIdNoCacheOrFail($userId);

        $this->assertEquals($return->getEmail(), $returnNoCache->getEmail());
    }

    /** @test */
    public function itShouldFindAUserByEmail(): void
    {
        $userEmail = new Email(self::USER_EMAIL);
        $return = $this->userRepository->findUserByEmailOrFail($userEmail);

        $this->assertEquals($userEmail, $return->getEmail());
    }

    /** @test */
    public function itShouldFailFindingAUserByEmail(): void
    {
        $this->expectException(DBNotFoundException::class);

        $userEmail = new Email(self::USER_EMAIL.'-Not valid email');
        $this->userRepository->findUserByEmailOrFail($userEmail);
    }

    /** @test */
    public function itShouldReturnManyUsersById(): void
    {
        $usersId = [
            ValueObjectFactory::createIdentifier('2606508b-4516-45d6-93a6-c7cb416b7f3f'),
            ValueObjectFactory::createIdentifier('b11c9be1-b619-4ef5-be1b-a1cd9ef265b7'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8f'),
        ];
        $return = $this->userRepository->findUsersByIdOrFail($usersId);
        $dbUsersIds = array_map(
            fn (User $user) => $user->getId(),
            $return
        );

        $this->assertContainsOnlyInstancesOf(User::class, $return);
        $this->assertCount(count($usersId), $return);

        foreach ($dbUsersIds as $dbUserId) {
            $this->assertContainsEquals($dbUserId, $usersId);
        }
    }

    /** @test */
    public function itShouldFailNoIds(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->userRepository->findUsersByIdOrFail([]);
    }

    /** @test */
    public function itShouldFailIdsDoesNotExistsInDataBase(): void
    {
        $this->expectException(DBNotFoundException::class);

        $usersId = [
            ValueObjectFactory::createIdentifier('0b13e52d-b058-32fb-8507-10dec634a07A'),
            ValueObjectFactory::createIdentifier('0b17ca3e-490b-3ddb-aa78-35b4ce668dcA'),
            ValueObjectFactory::createIdentifier('1befdbe2-9c14-42f0-850f-63e061e33b8A'),
        ];
        $this->userRepository->findUsersByIdOrFail($usersId);
    }

    /** @test */
    public function itShouldReturnManyUsersByName(): void
    {
        $usersName = [
            ValueObjectFactory::createName('Maria'),
            ValueObjectFactory::createName('Admin'),
            ValueObjectFactory::createName('Juanito'),
        ];
        $return = $this->userRepository->findUsersByNameOrFail($usersName);
        $dbUsersName = array_map(
            fn (User $user) => $user->getName(),
            $return
        );

        $this->assertContainsOnlyInstancesOf(User::class, $return);
        $this->assertCount(count($usersName), $return);

        foreach ($dbUsersName as $name) {
            $this->assertContainsEquals($name, $usersName);
        }
    }

    /** @test */
    public function itShouldFailNoNames(): void
    {
        $this->expectException(DBNotFoundException::class);

        $this->userRepository->findUsersByNameOrFail([]);
    }

    /** @test */
    public function itShouldFailNamesDoesNotExistsInDataBase(): void
    {
        $this->expectException(DBNotFoundException::class);

        $usersId = [
            ValueObjectFactory::createIdentifier('NameNotExisting1'),
            ValueObjectFactory::createIdentifier('NameNotExisting2'),
            ValueObjectFactory::createIdentifier('NameNotExisting3'),
        ];
        $this->userRepository->findUsersByNameOrFail($usersId);
    }

    /** @test */
    public function itShouldFindUsersNotActiveTimeActivationExpired(): void
    {
        $return = $this->userRepository->findUsersTimeActivationExpiredOrFail(-1);

        $this->assertCount(1, $return);

        foreach ($return as $user) {
            $this->assertEquals(self::USER_NOT_ACTIVE_ID, $user->getId());
        }
    }

    /** @test */
    public function itShouldFailFindingUsersNotActiveTimeActivationExpiredNoUsers(): void
    {
        $this->expectException(DBNotFoundException::class);
        $this->userRepository->findUsersTimeActivationExpiredOrFail(100);
    }

    private function getNewUser(): User
    {
        return User::fromPrimitives(
            '86c304df-a63e-4083-b1ee-add73be940a3',
            'new.user.email@host.com',
            'this is my password',
            'Alfredo',
            [USER_ROLES::NOT_ACTIVE]
        );
    }

    private function getExitsUser(): User
    {
        return User::fromPrimitives(
            '1befdbe2-9c14-42f0-850f-63e061e33b8f',
            'email.already.exists@host.com',
            'qwerty',
            'Juanito',
            [USER_ROLES::NOT_ACTIVE]
        );
    }
}
