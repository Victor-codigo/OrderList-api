<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Group\Domain\Model\Group;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Shop\Adapter\Database\Orm\Doctrine\Repository\ShopRepository;
use Shop\Domain\Model\Shop;
use Test\Unit\DataBaseTestCase;

class ShopRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const SHOP_ID_2 = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';
    private const SHOP_ID_3 = 'b9b1c541-d41e-4751-9ecb-4a1d823c0405';
    private const SHOP_ID_4 = 'cc7f5dd6-02ba-4bd9-b5c1-5b65d81e59a0';
    private const SHOP_NAME_EXISTS = 'Perico';

    private ShopRepository $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Shop::class);
    }

    private function getNewShop(): Shop
    {
        return Shop::fromPrimitives(
            '263878c8-20c4-457e-b1cf-7e0c1208a402',
            self::GROUP_ID,
            'Shop new name',
            'Shop new description',
            null
        );
    }

    private function getExistsShop(): Shop
    {
        return Shop::fromPrimitives(
            'e6c1d350-f010-403c-a2d4-3865c14630ec',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Shop 1 name',
            'Quae suscipit ea sit est exercitationem aliquid nobis. Qui quidem aut non quia cupiditate. Neque sunt aperiam cum quis quia aspernatur quia. Ratione enim eos rerum et. Ducimus voluptatem nam porro et est molestiae. Rerum perspiciatis et distinctio totam culpa et quaerat temporibus. Suscipit occaecati rerum molestiae voluptas odio eos. Sunt labore quia asperiores laborum. Unde explicabo et aspernatur vel odio modi qui. Ipsa recusandae eveniet doloribus quisquam. Nam aut ut omnis qui possimus.',
            null,
        );
    }

    private function getOtherExistsGroup(): Group
    {
        return Group::fromPrimitives(
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Group Other',
            GROUP_TYPE::GROUP,
            'This is a group of other users',
            'otherImage.png'
        );
    }

    /** @test */
    public function itShouldSaveTheShopInDatabase(): void
    {
        $shopNew = $this->getNewShop();
        $this->object->save($shopNew);

        /** @var Group $groupSaved */
        $shopSaved = $this->object->findOneBy(['id' => $shopNew->getId()]);

        $this->assertSame($shopNew, $shopSaved);
    }

    /** @test */
    public function itShouldFailShopIdAlreadyExists()
    {
        $this->expectException(DBUniqueConstraintException::class);

        $this->object->save($this->getExistsShop());
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

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->save($this->getNewShop());
    }

    /** @test */
    public function itShouldRemoveTheShop(): void
    {
        $shop = $this->getExistsShop();
        $shopToRemove = $this->object->findBy(['id' => $shop->getId()]);
        $this->object->remove($shopToRemove);

        $shopRemoved = $this->object->findBy(['id' => $shop->getId()]);

        $this->assertEmpty($shopRemoved);
    }

    /** @test */
    public function itShouldFailRemovingTheShopErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $group = $this->getNewShop();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$group]);
    }

    /** @test */
    public function itShouldGetTheShopsOfAGroupWhitAName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopName = ValueObjectFactory::createNameWithSpaces('Shop name 1');
        $return = $this->object->findShopsByGroupAndNameOrFail($groupId, $shopName);

        $this->assertCount(1, $return);

        foreach ($return as $shop) {
            $this->assertEquals($groupId, $shop->getGroupId());
        }
    }

    /** @test */
    public function itShouldFailGettingTheShopOfAGroupWithANameNoProduct(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopName = ValueObjectFactory::createNameWithSpaces('shop that not exists');

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopsByGroupAndNameOrFail($groupId, $shopName);
    }

    /** @test */
    public function itShouldGetAllShops(): void
    {
        $shopIds = [
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::SHOP_ID_2),
            ValueObjectFactory::createIdentifier(self::SHOP_ID_3),
        ];

        $return = $this->object->findShopsOrFail($shopIds);

        $this->assertCount(3, $return);

        foreach ($return as $shop) {
            $this->assertContainsEquals($shop->getId(), $shopIds);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findShopsOrFail(null, $groupId);

        $this->assertCount(4, $return);

        foreach ($return as $shop) {
            $this->assertEquals($shop->getGroupId(), $groupId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAProduct(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);

        $return = $this->object->findShopsOrFail(null, null, [$productId]);

        $this->assertCount(2, $return);

        $expectedShopsId = [
            self::SHOP_ID,
            'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupAndAProduct(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findShopsOrFail(null, $groupId, [$productId]);

        $this->assertCount(2, $return);

        $expectedShopsId = [
            self::SHOP_ID,
            'f6ae3da3-c8f2-4ccb-9143-0f361eec850e',
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupAndThatStartsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $return = $this->object->findShopsOrFail(null, $groupId, null, 'Sho');

        $this->assertCount(4, $return);

        $expectedShopsId = [
            self::SHOP_ID,
            self::SHOP_ID_2,
            self::SHOP_ID_3,
            self::SHOP_ID_4,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsThatStartsWith(): void
    {
        $return = $this->object->findShopsOrFail(null, null, null, 'Sho');

        $this->assertCount(4, $return);

        $expectedShopsId = [
            self::SHOP_ID,
            self::SHOP_ID_2,
            self::SHOP_ID_3,
            self::SHOP_ID_4,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldFailGettingAllShopsOfAGroupNotFound(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $groupId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopsOrFail(null, $groupId, [$productId]);
    }

    /** @test */
    public function itShouldFailGettingAllShopsOfAShopNotFound(): void
    {
        $productId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopsOrFail(null, $groupId, [$productId]);
    }

    /** @test */
    public function itShouldFailGettingShopsThereAreNot(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopsOrFail([$shopId]);
    }
}
