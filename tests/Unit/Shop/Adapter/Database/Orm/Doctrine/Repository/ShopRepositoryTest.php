<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
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

    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_ID_2 = 'fdb242b4-bac8-4463-88d0-0941bb0beee0';
    private const string PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const string SHOP_ID = 'b9b1c541-d41e-4751-9ecb-4a1d823c0405';
    private const string SHOP_ID_3 = 'f6ae3da3-c8f2-4ccb-9143-0f361eec850e';
    private const string SHOP_ID_2 = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const string SHOP_ID_4 = 'cc7f5dd6-02ba-4bd9-b5c1-5b65d81e59a0';

    private ShopRepository $object;

    #[\Override]
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
            'Shop new address',
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
            'Shop 1 address',
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
        /** @var Group $shopSaved */
        $shopSaved = $this->object->findOneBy(['id' => $shopNew->getId()]);

        $this->assertSame($shopNew, $shopSaved);
    }

    /** @test */
    public function itShouldFailShopIdAlreadyExists(): void
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
    public function itShouldGetAllShopsOfAGroupOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNames = [
            ValueObjectFactory::createNameWithSpaces('Shop name 1'),
            ValueObjectFactory::createNameWithSpaces('Shop name 2'),
            ValueObjectFactory::createNameWithSpaces('Shop name 3'),
            ValueObjectFactory::createNameWithSpaces('Shop name 4'),
        ];
        $return = $this->object->findShopsOrFail($groupId, null, null, true);
        $return = iterator_to_array($return);

        $this->assertCount(4, $return);

        foreach ($shopNames as $key => $shopNamesExpected) {
            $this->assertEquals($shopNamesExpected, $return[$key]->getName());
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupOrderDesc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNames = [
            ValueObjectFactory::createNameWithSpaces('Shop name 4'),
            ValueObjectFactory::createNameWithSpaces('Shop name 3'),
            ValueObjectFactory::createNameWithSpaces('Shop name 2'),
            ValueObjectFactory::createNameWithSpaces('Shop name 1'),
        ];
        $return = $this->object->findShopsOrFail($groupId, null, null, false);
        $return = iterator_to_array($return);

        $this->assertCount(4, $return);

        foreach ($shopNames as $key => $shopNamesExpected) {
            $this->assertEquals($shopNamesExpected, $return[$key]->getName());
        }
    }

    /** @test */
    public function itShouldGetShopsOfAGroupByShopIdOrderAsc(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopIds = [
            ValueObjectFactory::createIdentifier(self::SHOP_ID),
            ValueObjectFactory::createIdentifier(self::SHOP_ID_2),
            ValueObjectFactory::createIdentifier(self::SHOP_ID_3),
        ];
        $shopNames = [
            ValueObjectFactory::createNameWithSpaces('Shop name 1'),
            ValueObjectFactory::createNameWithSpaces('Shop name 2'),
            ValueObjectFactory::createNameWithSpaces('Shop name 3'),
        ];

        $return = $this->object->findShopsOrFail($groupId, $shopIds, null, true);
        $return = iterator_to_array($return);

        $this->assertCount(3, $return);

        foreach ($shopNames as $key => $shopNamesExpected) {
            $this->assertEquals($shopNamesExpected, $return[$key]->getName());
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupByProductId(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);

        $return = $this->object->findShopsOrFail($groupId, null, [$productId]);

        $this->assertCount(3, $return);

        $expectedShopsId = [
            self::SHOP_ID,
            self::SHOP_ID_2,
            self::SHOP_ID_3,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfGroupByShopIdANdProductId(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID_2);
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);

        $return = $this->object->findShopsOrFail($groupId, [$shopId], [$productId]);

        $this->assertCount(1, $return);

        $expectedShopsId = [
            self::SHOP_ID_2,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetShopsOfAGroupByName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $return = $this->object->findShopByShopNameOrFail(
            $groupId,
            ValueObjectFactory::createNameWithSpaces('Shop name 3'),
            true
        );

        $this->assertCount(1, $return);

        $expectedShopsId = [
            self::SHOP_ID,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupThatNameStartsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::STARTS_WITH),
            ValueObjectFactory::createNameWithSpaces('Shop')
        );
        $return = $this->object->findShopByShopNameFilterOrFail($groupId, $shopNameFilter, true);

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
    public function itShouldGetAllShopsOfAGroupThatNameEndsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::ENDS_WITH),
            ValueObjectFactory::createNameWithSpaces('name 1')
        );
        $return = $this->object->findShopByShopNameFilterOrFail($groupId, $shopNameFilter, true);

        $this->assertCount(1, $return);

        $expectedShopsId = [
            self::SHOP_ID_2,
        ];

        foreach ($return as $shop) {
            $this->assertContains($shop->getId()->getValue(), $expectedShopsId);
        }
    }

    /** @test */
    public function itShouldGetAllShopsOfAGroupAndThatContains(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            ValueObjectFactory::createNameWithSpaces('name')
        );
        $return = $this->object->findShopByShopNameFilterOrFail($groupId, $shopNameFilter, true);

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
        $groupId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopsOrFail($groupId);
    }

    /** @test */
    public function itShouldFindAllShopsOfAGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifier(self::GROUP_ID_2),
        ];

        $return = $this->object->findGroupsShopsOrFail($groupsId);
        $expect = $this->object->findBy([
            'groupId' => $groupsId,
        ]);

        $this->assertEquals($expect, iterator_to_array($return));
    }

    /** @test */
    public function itShouldFailNoShopsFoundInGroups(): void
    {
        $groupsId = [
            ValueObjectFactory::createIdentifier('6f76bd87-7382-46fc-9746-ba983dbbfeba'),
        ];

        $this->expectException(DBNotFoundException::class);
        $this->object->findGroupsShopsOrFail($groupsId);
    }

    /** @test */
    public function itShouldFailGettingShopsOfAGroupByNameNotFound(): void
    {
        $shopName = ValueObjectFactory::createNameWithSpaces('name not found');
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopByShopNameOrFail($groupId, $shopName, true);
    }

    /** @test */
    public function itShouldFailGettingShopNameFilterNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $shopNameFilter = new Filter(
            'shop_name',
            ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::CONTAINS),
            ValueObjectFactory::createNameWithSpaces('shop name not found')
        );

        $this->expectException(DBNotFoundException::class);
        $this->object->findShopByShopNameFilterOrFail($groupId, $shopNameFilter);
    }
}
