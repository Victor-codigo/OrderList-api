<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\SetProductShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\SetProductShopPrice\Dto\SetProductShopPriceDto;
use Product\Domain\Service\SetProductShopPrice\SetProductShopPriceService;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class SetProductShopPriceServiceTest extends TestCase
{
    private SetProductShopPriceService $object;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|ProductRepositoryInterface $productRepository;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|PaginatorInterface $productsShopsPaginator;
    private MockObject|PaginatorInterface $productsPaginator;
    private MockObject|PaginatorInterface $shopsPaginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->productsShopsPaginator = $this->createMock(PaginatorInterface::class);
        $this->productsPaginator = $this->createMock(PaginatorInterface::class);
        $this->shopsPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new SetProductShopPriceService(
            $this->productShopRepository,
            $this->productRepository,
            $this->shopRepository
        );
    }

    /**
     * @return Identifier[]
     */
    private function getIdentifier(array $ids): array
    {
        return array_map(
            fn (string $id) => ValueObjectFactory::createIdentifier($id),
            $ids
        );
    }

    /**
     * @return Money[]
     */
    private function getPrices(array $prices): array
    {
        return array_map(
            fn (float $price) => ValueObjectFactory::createMoney($price),
            $prices
        );
    }

    private function createProducts(Identifier $groupId, array $productsId): array
    {
        $productsCreateCallBack = function (Identifier $productId) use ($groupId) {
            $productIdString = $productId->getValue();

            return Product::fromPrimitives(
                $productIdString,
                $groupId->getValue(),
                $productIdString.' name',
                $productIdString.' description',
                null
            );
        };

        return array_map($productsCreateCallBack, $productsId);
    }

    private function createShops(Identifier $groupId, array $shopsId): array
    {
        $shopsCreateCallback = function (Identifier $shopId) use ($groupId) {
            $shopIdString = $shopId->getValue();

            return Shop::fromPrimitives(
                $shopIdString,
                $groupId->getValue(),
                $shopIdString.' name',
                $shopIdString.' description',
                null
            );
        };

        return array_map($shopsCreateCallback, $shopsId);
    }

    /**
     * @param Product[] $products
     * @param Shop[]    $shops
     * @param Money[]   $prices
     *
     * @return ProductShop[]
     */
    private function createProductsShops(array $products, array $shops, array $prices): array
    {
        return array_map(
            fn (Product $product, int $index) => new ProductShop($product, $shops[$index], $prices[$index]),
            $products, array_keys($products)
        );
    }

    /**
     * @param string[]|null $productsId
     * @param string[]|null $shopsIds
     * @param float[]|null  $prices
     *
     * @return array<{0: Identifier[], 1: Identifier[], 2: Money[]}>
     */
    private function createData(array|null $productsId, array|null $shopsIds, array|null $prices): array
    {
        $productsId ??= ['product id 1', 'product id 2', 'product id 3'];
        $shopsIds ??= ['shop id 1', 'shop id 2', 'shop id 3'];
        $prices ??= [1, 2, 3];

        return [
             $this->getIdentifier($productsId),
             $this->getIdentifier($shopsIds),
             $this->getPrices($prices),
        ];
    }

    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     *
     * @return array<{0: Product[], 1: Shop[]}>
     */
    private function createProductsAndShops(Identifier $groupId, array $productsId, array $shopsId): array
    {
        return [
            $this->createProducts($groupId, $productsId),
            $this->createShops($groupId, $shopsId),
        ];
    }

    /**
     * @param ProductShop[] $productsShops1
     * @param ProductShop[] $productsShops2
     */
    private function compareProductShop(array $productsShops1, array $productsShops2): void
    {
        $this->assertCount(count($productsShops1), $productsShops2);

        foreach ($productsShops1 as $key => $productShop1) {
            $this->assertEquals($productShop1->getProductId(), $productsShops2[$key]->getProductId());
            $this->assertEquals($productShop1->getShopId(), $productsShops2[$key]->getShopId());
            $this->assertEquals($productShop1->getPrice(), $productsShops2[$key]->getPrice());
        }
    }

    /** @test */
    public function itShouldSetProductsShopsAndPricesAllNewByShopId(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productNewId = ValueObjectFactory::createIdentifier(null);
        $shopIdNew = ValueObjectFactory::createIdentifier('shop id 1 new ');

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            ['product id 1', 'product id 2', 'product id 3'],
            [$shopIdNew, $shopIdNew, $shopIdNew],
            [11, 22, 33]
        );
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);
        $productsShopsNew = $this->createProductsShops($productsNew, $shopsNew, $pricesNew);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with(null, [$shopIdNew], $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($productsDb, $productsNew)));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($shopsDb, $shopsNew)));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, $productsShopsNew) || true));

        $input = new SetProductShopPriceDto($groupId, $productNewId, $shopIdNew, $productsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->compareProductShop($productsShopsNew, $return);
    }

    /** @test */
    public function itShouldSetProductsShopsAndPricesAllNew(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1 new');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [$productIdNew, $productIdNew, $productIdNew],
            ['shop id 1 new ', 'shop id 2 new', 'shop id 3 new'],
            [11, 22, 33]
        );
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);
        $productsShopsNew = $this->createProductsShops($productsNew, $shopsNew, $pricesNew);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($productsDb, $productsNew)));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($shopsDb, $shopsNew)));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, $productsShopsNew) || true));

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->compareProductShop($productsShopsNew, $return);
    }

    /** @test */
    public function itShouldModifyAllProductsShopsAndPrices(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);
        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(
            [$productIdNew, $productIdNew, $productIdNew],
            null,
            null
        );
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            $productsIdDb,
            null,
            [11, 22, 33]
        );

        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        $productsShopsDb = $this->createProductsShops($productsDb, $shopsDb, $pricesDb);
        $productsShopsNew = $productsShopsDb;

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsShopsDb));

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, $productsShopsNew) || true));

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->compareProductShop($productsShopsNew, $return);
    }

    /** @test */
    public function itShouldRemoveAllProductsShopsAndPrices(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);
        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = [[], [], []];
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        $productsShopsDb = $this->createProductsShops($productsDb, $shopsDb, $pricesDb);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsShopsDb));

        $this->productRepository
            ->expects($this->never())
            ->method('findProductsOrFail');

        $this->productsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->shopRepository
            ->expects($this->never())
            ->method('findShopsOrFail');

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, $productsShopsDb) || true));

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([]);

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldSetModifyAndRemoveProductsShopsAndPrices(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(
            [$productIdNew, $productIdNew, $productIdNew],
            null,
            null
        );
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [
                $productIdNew,
                $productIdNew,
                $productIdNew,
                $productIdNew,
            ], [
                'shop id 2',
                'shop id new 4',
                'shop id 1',
                'shop id new 5',
            ], [
                22,
                44,
                11,
                55,
            ]
        );
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        $productsShopsDb = $this->createProductsShops($productsDb, $shopsDb, $pricesDb);
        $productsShopsNew = $this->createProductsShops($productsNew, $shopsNew, $pricesNew);
        $productsDb = array_merge($productsDb, [$productsNew[1], $productsNew[3]]);
        $shopsDb = array_merge($shopsDb, [$shopsNew[1], $shopsNew[3]]);
        $pricesDb = array_merge($pricesDb, [$pricesNew[1], $pricesNew[3]]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsShopsDb));

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, [$productsIdNew[1], $productsIdNew[3]])
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsDb));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, [$shopsIdNew[1], $shopsIdNew[3]])
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shopsNew));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, [$productsShopsDb[2]]) || true));

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (array $productShopToSave) => $this->compareProductShop($productShopToSave, [$productsShopsDb[0], $productsShopsDb[1], $productsShopsNew[1], $productsShopsNew[3]]) || true));

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->compareProductShop([$productsShopsDb[0], $productsShopsDb[1], $productsShopsNew[1], $productsShopsNew[3]], $return);
    }

    /** @test */
    public function itShouldFailSettingProductsShopsAndPricesProductsAndShopsNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1 new');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [
                $productIdNew,
                $productIdNew,
                $productIdNew,
            ],
            null,
            [11, 22, 33]
        );

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willThrowException(new DBNotFoundException());

        $this->productsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willThrowException(new DBNotFoundException());

        $this->shopsPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([]);

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailSettingProductsShopsAndPricesProductsIdNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id not found 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [$productIdNew, $productIdNew],
            ['shop id 1', 'shop id 1'],
            [11, 22]
        );
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsDb));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($shopsDb, $shopsNew)));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([]);

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailSettingProductsShopsAndPricesShopsIdNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [$productIdNew, $productIdNew],
            ['shop id not found 1', 'shop id not found 1'],
            [11, 22]
        );
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willThrowException(new DBNotFoundException());

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator(array_merge($productsDb, $productsNew)));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shopsDb));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([]);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([]);

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailModifyingProductsShopsAndPricesShopsNotFound(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productIdNew = ValueObjectFactory::createIdentifier('product id 1');
        $shopIdNew = ValueObjectFactory::createIdentifier(null);

        [$productsIdDb, $shopsIdDb, $pricesDb] = $this->createData(null, null, null);
        [$productsIdNew, $shopsIdNew, $pricesNew] = $this->createData(
            [$productIdNew, $productIdNew, $productIdNew],
            ['shop id not found 1', 'shop id not found 1', 'shop id not found 3'],
            [11, 22, 33]
        );
        [$productsDb, $shopsDb] = $this->createProductsAndShops($groupId, $productsIdDb, $shopsIdDb);
        [$productsNew, $shopsNew] = $this->createProductsAndShops($groupId, $productsIdNew, $shopsIdNew);
        $productsShopsDb = $this->createProductsShops($productsDb, $shopsDb, $pricesDb);
        $productsShopsNew = $this->createProductsShops($productsNew, $shopsNew, $pricesNew);

        $this->productShopRepository
            ->expects($this->once())
            ->method('findProductsAndShopsOrFail')
            ->with([$productIdNew], null, $groupId)
            ->willReturn($this->productsShopsPaginator);

        $this->productsShopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsShopsDb));

        $this->productRepository
            ->expects($this->once())
            ->method('findProductsOrFail')
            ->with($groupId, $productsIdNew)
            ->willReturn($this->productsPaginator);

        $this->productsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($productsDb));

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with($groupId, $shopsIdNew)
            ->willReturn($this->shopsPaginator);

        $this->shopsPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shopsDb));

        $this->productShopRepository
            ->expects($this->once())
            ->method('remove')
            ->with($productsShopsDb);

        $this->productShopRepository
            ->expects($this->once())
            ->method('save')
            ->with([]);

        $input = new SetProductShopPriceDto($groupId, $productIdNew, $shopIdNew, $shopsIdNew, $pricesNew);
        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }
}
