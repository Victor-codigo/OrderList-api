<?php

declare(strict_types=1);

namespace Test\Unit\Product\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\Persistence\ObjectManager;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Adapter\Database\Orm\Doctrine\Repository\ProductShopRepository;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Test\Unit\DataBaseTestCase;

class ProductShopRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const string GROUP_OTHER_ID = '30c6dada-4f30-4fdc-9547-2682e4eb82ca';
    private const string SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const string PRODUCT_ID = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';

    private ProductShopRepository $object;
    private ProductRepositoryInterface $productRepository;
    private ShopRepositoryInterface $shopRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(ProductShop::class);
        $this->productRepository = $this->entityManager->getRepository(Product::class);
        $this->shopRepository = $this->entityManager->getRepository(Shop::class);
    }

    private function getNewProductShop(float $price, UNIT_MEASURE_TYPE $unit): ProductShop
    {
        $product = $this->productRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::PRODUCT_ID)]);
        $shop = $this->shopRepository->findOneBy(['id' => ValueObjectFactory::createIdentifier(self::SHOP_ID)]);

        return new ProductShop(
            $product,
            $shop,
            ValueObjectFactory::createMoney($price),
            ValueObjectFactory::createUnit($unit)
        );
    }

    /** @test */
    public function itShouldFailSavingDataBaseError(): void
    {
        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);

        $this->expectException(DBConnectionException::class);
        $this->object->save([$this->getNewProductShop(1.3, UNIT_MEASURE_TYPE::UNITS)]);
    }

    /** @test */
    public function itShouldFindAllShopsOfAProduct(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $return = iterator_to_array(
            $this->object->findProductsAndShopsOrFail([$productId])
        )[0];

        $productExpected = $this->object->findOneBy(['productId' => $productId]);

        $this->assertEquals($productExpected, $return);
    }

    /** @test */
    public function itShouldFindAllProductsOfAShop(): void
    {
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $return = iterator_to_array(
            $this->object->findProductsAndShopsOrFail(null, [$shopId])
        )[0];

        $shopExpected = $this->object->findOneBy(['shopId' => $shopId]);

        $this->assertEquals($shopExpected, $return);
    }

    /** @test */
    public function itShouldFindAProductsOfAShop(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $return = iterator_to_array(
            $this->object->findProductsAndShopsOrFail([$productId], [$shopId])
        )[0];

        $shopExpected = $this->object->findOneBy([
            'productId' => $productId,
            'shopId' => $shopId,
        ]);

        $this->assertEquals($shopExpected, $return);
    }

    /** @test */
    public function itShouldFindAProductsOfAShopOfAGroup(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $return = iterator_to_array(
            $this->object->findProductsAndShopsOrFail([$productId], [$shopId], $groupId)
        )[0];

        $shopExpected = $this->object->findOneBy([
            'productId' => $productId,
            'shopId' => $shopId,
        ]);

        $this->assertEquals($shopExpected, $return);
    }

    /** @test */
    public function itShouldFailFindAProductsOrAShopsNotFound(): void
    {
        $productId = ValueObjectFactory::createIdentifier(self::PRODUCT_ID);
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_OTHER_ID);

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsAndShopsOrFail([$productId], [$shopId], $groupId);
    }
}
