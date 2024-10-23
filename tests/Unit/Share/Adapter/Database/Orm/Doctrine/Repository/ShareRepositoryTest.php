<?php

declare(strict_types=1);

namespace Test\Unit\Share\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use ListOrders\Adapter\Database\Orm\Doctrine\Repository\ListOrdersRepository;
use ListOrders\Domain\Model\ListOrders;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Share\Adapter\Database\Orm\Doctrine\Repository\ShareRepository;
use Share\Domain\Model\Share;
use Test\Unit\DataBaseTestCase;

class ShareRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string SHARE_ID_NEW = '79bf5849-c849-4ce3-bb42-121590462445';
    private const string SHARE_ID_EXIST = '72b37f9c-ff55-4581-a131-4270e73012a2';
    private const string SHARE_ID_EXIST_2 = '8aaa96f5-cc54-45cb-bf43-f9b8fe256696';
    private const string SHARE_ID_EXIST_3 = '5552b4a6-8326-462a-a42b-f60b33640aef';
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3f';

    private ShareRepository $object;
    private ListOrdersRepository $listOrdersRepository;
    private MockObject&ConnectionException $connectionException;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Share::class);
        $this->listOrdersRepository = $this->entityManager->getRepository(ListOrders::class);
        $this->connectionException = $this->createMock(ConnectionException::class);
    }

    private function createShare(ListOrders $listOrders, Identifier $userId, ?Identifier $id): Share
    {
        return new Share(
            $id ?? ValueObjectFactory::createIdentifier(self::SHARE_ID_NEW),
            $userId,
            $listOrders->getId(),
            $listOrders->getGroupId(),
            new \DateTime()
        );
    }

    #[Test]
    public function itShouldSaveShared(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $share = $this->createShare($listOrdersDatabase, $userId, null);

        $this->object->save($share);

        /** @var Share $expected */
        $expected = $this->object->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::SHARE_ID_NEW)]);

        $this->assertEquals(self::SHARE_ID_NEW, $expected->getId()->getValue());
    }

    #[Test]
    public function itShouldFailSaveSharedAlreadyExists(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $share = $this->createShare($listOrdersDatabase, $userId, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        $this->expectException(DBUniqueConstraintException::class);
        $this->object->save($share);
    }

    #[Test]
    public function itShouldFailSavingShareInDataBaseError(): void
    {
        $listOrdersDatabase = $this->listOrdersRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::LIST_ORDERS_ID)]);
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $share = $this->createShare($listOrdersDatabase, $userId, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        $this->expectException(DBConnectionException::class);

        /** @var MockObject&ObjectManager $objectManagerMock */
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
        $userId = ValueObjectFactory::createIdentifier(self::USER_ID);
        $share = $this->createShare($listOrdersDatabase, $userId, ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST));

        /** @var MockObject&ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException($this->connectionException);

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$share]);
    }

    #[Test]
    public function itShouldFindRecurseById(): void
    {
        $RecoursesId = [
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
        ];
        $return = $this->object->findSharedRecursesByIdOrFail($RecoursesId);
        $RecoursesIdReturned = array_map(
            fn (Share $share): Identifier => $share->getId(),
            iterator_to_array($return)
        );

        $this->assertCount(3, $return);
        $this->assertEqualsCanonicalizing($RecoursesId, $RecoursesIdReturned);
    }

    #[Test]
    public function itShouldFailFindingRecurseByIdsEmpty(): void
    {
        $RecoursesId = [];
        $this->expectException(DBNotFoundException::class);
        $this->object->findSharedRecursesByIdOrFail($RecoursesId);
    }

    #[Test]
    public function itShouldFailFindingRecurseByIdNotFound(): void
    {
        $RecoursesId = [
            ValueObjectFactory::createIdentifier('not found id'),
        ];
        $this->expectException(DBNotFoundException::class);
        $this->object->findSharedRecursesByIdOrFail($RecoursesId);
    }

    #[Test]
    public function itShouldFindRecoursesExpired(): void
    {
        $RecoursesIdExpected = [
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
        ];

        $return = $this->object->findSharedRecursesExpiredOrFail();

        $recoursesFoundId = array_map(
            fn (Share $share): Identifier => $share->getId(),
            iterator_to_array($return)
        );
        $this->assertEqualsCanonicalizing($RecoursesIdExpected, $recoursesFoundId);
    }

    #[Test]
    #[Depends('itShouldRemoveSomeShared')]
    public function itShouldFAilFindingRecoursesExpiredNotFound(): void
    {
        $RecoursesId = [
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
        ];

        $recoursesFound = $this->object->findBy(['id' => $RecoursesId]);
        $this->object->remove(iterator_to_array($recoursesFound));

        $this->expectException(DBNotFoundException::class);
        $this->object->findSharedRecursesExpiredOrFail();
    }
}
