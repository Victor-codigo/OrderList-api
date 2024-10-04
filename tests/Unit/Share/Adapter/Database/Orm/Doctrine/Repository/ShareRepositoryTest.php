<?php

declare(strict_types=1);

namespace Test\Unit\Share\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersRepository;
use ListOrders\Domain\Model\ListOrders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Share\Adapter\Database\Orm\Doctrine\Repository\ShareRepository;
use Share\Domain\Model\Share;
use Test\Unit\DataBaseTestCase;
use User\Adapter\Database\Orm\Doctrine\Repository\UserRepository;
use User\Domain\Model\User;

class ShareRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string SHARE_ID_NEW = '79bf5849-c849-4ce3-bb42-121590462445';
    private const string SHARE_ID_EXIST = '72b37f9c-ff55-4581-a131-4270e73012a2';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';
    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string CREATION_DATE = '2024-10-1 12:00';

    private ShareRepository $object;
    private ListOrdersRepository $listOrdersRepository;
    private UserRepository $userRepository;
    private MockObject|ConnectionException $connectionException;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Share::class);
        $this->listOrdersRepository = $this->entityManager->getRepository(ListOrders::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->connectionException = $this->createMock(ConnectionException::class);
    }

    private function createListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            self::LIST_ORDERS_ID,
            self::GROUP_ID,
            self::USER_ID,
            'list orders',
            'list orders description',
            new \DateTime(self::CREATION_DATE)
        );
    }

    private function createUser(): User
    {
        return User::fromPrimitives(
            self::USER_ID,
            'user@email.com',
            'user password',
            'user name',
            [USER_ROLES::USER]
        );
    }

    private function createShare(ListOrders $listOrders, User $user, ?Identifier $id): Share
    {
        return new Share(
            $id ?? ValueObjectFactory::createIdentifier(self::SHARE_ID_NEW),
            $listOrders,
            $user,
            new \DateTime()
        );
    }

    #[Test]
    public function itShouldSaveShared(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userDatabase = $this->userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::USER_ID)]);
        $share = $this->createShare($listOrdersDatabase, $userDatabase, null);

        $this->object->save($share);

        /** @var Share $exception */
        $expected = $this->object->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::SHARE_ID_NEW)]);

        $this->assertEquals(self::SHARE_ID_NEW, $expected->getId()->getValue());
    }

    #[Test]
    public function itShouldFailSaveSharedAlreadyExists(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userDatabase = $this->userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::USER_ID)]);
        $share = $this->createShare($listOrdersDatabase, $userDatabase, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save($share);
    }

    #[Test]
    public function itShouldFailSavingShareInDataBaseError(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userDatabase = $this->userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::USER_ID)]);
        $share = $this->createShare($listOrdersDatabase, $userDatabase, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        $this->expectException(DBConnectionException::class);

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->save($share);
    }

    #[Test]
    public function itShouldRemoveSomeShared(): void
    {
        $share = $this->object->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST)]);

        $this->object->remove([$share]);

        $sharedDataBaseExpected = $this->object->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST)]);

        $this->assertNull($sharedDataBaseExpected);
    }

    #[Test]
    public function itShouldFailRemovingSomeShared(): void
    {
        $this->expectException(DBConnectionException::class);

        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userDatabase = $this->userRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::USER_ID)]);
        $share = $this->createShare($listOrdersDatabase, $userDatabase, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$share]);
    }
}
