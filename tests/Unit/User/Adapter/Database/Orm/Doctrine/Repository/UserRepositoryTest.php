<?php

declare(strict_types=1);

namespace Test\Unit\User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Test\Unit\DataBaseTestCase;
use User\Adapter\Database\Orm\Doctrine\Repository\UserRepository;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\User;

class UserRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private UserRepository $userRepository;
    private MockObject|ManagerRegistry $managerRegistry;

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

        $this->userRepository->save($this->getExsitsUser());
    }

    /** @test */
    public function itShouldFailDataBaseError(): void
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

    private function getNewUser(): User
    {
        return User::fromPrimitives(
            '86c304df-a63e-4083-b1ee-add73be940a3',
            'new.user.email@host.com',
            'this is my passorwd',
            'Alfredo',
            [USER_ROLES::NOT_ACTIVE]
        );
    }

    private function getExsitsUser(): User
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
