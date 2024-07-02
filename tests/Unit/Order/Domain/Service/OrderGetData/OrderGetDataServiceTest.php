<?php

declare(strict_types=1);

namespace Test\Unit\Order\Domain\Service\OrderGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Order\Domain\Service\OrderGetData\OrderGetDataService;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Shop\Domain\Model\Shop;
use Test\Unit\DataBaseTestCase;

class OrderGetDataServiceTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const string GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const array ORDERS_ID = [
        '9a48ac5b-4571-43fd-ac80-28b08124ffb8',
        'a0b4760a-9037-477a-8b84-d059ae5ee7e9',
        'c3734d1c-8b18-4bfd-95aa-06a261476d9d',
        'd351adba-c566-4fa5-bb5b-1a6f73b1d72f',
    ];
    private const string APP_PROTOCOL_AND_DOMAIN = 'appProtocolAndDomain';
    private const string PRODUCT_PUBLIC_PATH = '/productPublicPath';
    private const string SHOP_PUBLIC_PATH = '/shopPublicPath';

    private OrderGetDataService $object;
    private MockObject|OrderRepositoryInterface $orderRepository;
    private MockObject|ProductShopRepositoryInterface $productShopRepository;
    private MockObject|PaginatorInterface $ordersPaginator;
    private MockObject|PaginatorInterface $productsShopsPaginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productShopRepository = $this->createMock(ProductShopRepositoryInterface::class);
        $this->ordersPaginator = $this->createMock(PaginatorInterface::class);
        $this->productsShopsPaginator = $this->createMock(PaginatorInterface::class);
        $this->object = new OrderGetDataService($this->orderRepository, self::PRODUCT_PUBLIC_PATH, self::SHOP_PUBLIC_PATH, self::APP_PROTOCOL_AND_DOMAIN);
    }

    /**
     * @return Identifier[]
     */
    private function getOrdersIdentifiers(): array
    {
        return array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            self::ORDERS_ID
        );
    }

    private function getListOrders(): ListOrders
    {
        return ListOrders::fromPrimitives(
            '0f782e29-91ef-496c-b088-cd3cf4467c81',
            '7992d525-38f3-4864-9518-22ecf4190cea',
            'c76402a0-e650-418e-b369-e846d155a5d8',
            'List orders 1',
            'List orders 1 description',
            null
        );
    }

    /**
     * @return Product[]
     */
    private function getProducts(): array
    {
        return [
            Product::fromPrimitives(
                'cfc7f721-da43-45e1-b98c-25454fe8196e',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'Product 1',
                'Product 1 description',
                'fileName.file'
            ),
            Product::fromPrimitives(
                'c00ad57c-5c6b-4abd-9b14-9dfa7a196058',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'Product 2',
                'Product 2 description',
                null
            ),
        ];
    }

    /**
     * @return Product[]
     */
    private function getProductsExpected(): array
    {
        $products = $this->getProducts();

        return array_map(
            function (Product $product) {
                if (!$product->getImage()->isNull()) {
                    $product->setImage(
                        ValueObjectFactory::createPath(self::APP_PROTOCOL_AND_DOMAIN.self::PRODUCT_PUBLIC_PATH.'/'.$product->getImage()->getValue())
                    );
                }

                return $product;
            },
            $products
        );
    }

    /**
     * @return Shop[]
     */
    private function getShops(): array
    {
        return [
            Shop::fromPrimitives(
                '3d984e2d-74a3-4977-a927-65d3add38c0f',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'Shop 1',
                'Shop 1 description',
                'fileName.file'
            ),
            Shop::fromPrimitives(
                '94a8f497-5c8b-44b7-9e26-8efe36044f8c',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'Shop 2',
                'Shop 2 description',
                null
            ),
        ];
    }

    /**
     * @return Shop[]
     */
    private function getShopsExpected(): array
    {
        $shops = $this->getShops();

        return array_map(
            function (Shop $shop) {
                if (!$shop->getImage()->isNull()) {
                    $shop->setImage(
                        ValueObjectFactory::createPath(self::APP_PROTOCOL_AND_DOMAIN.self::PRODUCT_PUBLIC_PATH.'/'.$shop->getImage()->getValue())
                    );
                }

                return $shop;
            },
            $shops
        );
    }

    /**
     * @return Order[]
     */
    private function getOrders(): array
    {
        $listOrders = $this->getListOrders();
        $product = $this->getProducts();
        $shops = $this->getShops();

        $product[0]->setProductShop([
            ProductShop::fromPrimitives($product[0], $shops[1], 20.30, UNIT_MEASURE_TYPE::UNITS),
            ProductShop::fromPrimitives($product[0], $shops[0], 20.30, UNIT_MEASURE_TYPE::UNITS),
        ]);

        $product[1]->setProductShop([
            ProductShop::fromPrimitives($product[1], $shops[0], 10.5, UNIT_MEASURE_TYPE::UNITS),
            ProductShop::fromPrimitives($product[1], $shops[1], 10.5, UNIT_MEASURE_TYPE::UNITS),
        ]);

        return [
            Order::fromPrimitives(
                '41d14a8a-9bdc-4a77-898e-9972355c6b2f',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'c76402a0-e650-418e-b369-e846d155a5d8',
                'Order 1 description',
                10,
                false,
                $listOrders,
                $product[0],
                null
            ),
            Order::fromPrimitives(
                'f76f65dc-92b7-450a-bbf9-224764eb22f6',
                '7992d525-38f3-4864-9518-22ecf4190cea',
                'c76402a0-e650-418e-b369-e846d155a5d8',
                'Order 2 description',
                20,
                true,
                $listOrders,
                $product[1],
                $shops[1]
            ),
        ];
    }

    /**
     * @return Order[]
     */
    private function getOrdersExpected(): array
    {
        $orders = $this->getOrders();

        return array_map(
            function (Order $order) {
                if (!$order->getProduct()->getImage()->isNull()) {
                    $order->getProduct()->setImage(
                        ValueObjectFactory::createPath(self::APP_PROTOCOL_AND_DOMAIN.self::PRODUCT_PUBLIC_PATH.'/'.$order->getProduct()->getImage()->getValue())
                    );
                }

                if (null !== $order->getShop() && !$order->getShop()->getImage()->isNull()) {
                    $order->getShop()->setImage(
                        ValueObjectFactory::createPath(self::APP_PROTOCOL_AND_DOMAIN.self::SHOP_PUBLIC_PATH.'/'.$order->getShop()->getImage()->getValue())
                    );
                }

                return $order;
            },
            $orders
        );
    }

    private function getProductsShops(): array
    {
        $products = $this->getProducts();
        $shops = $this->getShops();

        return [
            ProductShop::fromPrimitives(
                $products[0],
                $shops[0],
                null,
                null
            ),
            ProductShop::fromPrimitives(
                $products[1],
                $shops[1],
                40,
                UNIT_MEASURE_TYPE::KG
            ),
        ];
    }

    /**
     * @param Order[] $orders
     *
     * @return Identifier[]
     */
    private function getShopsId(array $orders): array
    {
        return array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
    }

    private function assertOrderDataIsOk(Order $orderExpected, array $orderActual): void
    {
        $this->assertArrayHasKey('id', $orderActual);
        $this->assertArrayHasKey('user_id', $orderActual);
        $this->assertArrayHasKey('group_id', $orderActual);
        $this->assertArrayHasKey('list_orders_id', $orderActual);
        $this->assertArrayHasKey('description', $orderActual);
        $this->assertArrayHasKey('amount', $orderActual);
        $this->assertArrayHasKey('bought', $orderActual);
        $this->assertArrayHasKey('created_on', $orderActual);
        $this->assertArrayHasKey('product', $orderActual);
        $this->assertArrayHasKey('shop', $orderActual);
        $this->assertArrayHasKey('productShop', $orderActual);

        $this->assertArrayHasKey('id', $orderActual['product']);
        $this->assertArrayHasKey('name', $orderActual['product']);
        $this->assertArrayHasKey('description', $orderActual['product']);
        $this->assertArrayHasKey('image', $orderActual['product']);
        $this->assertArrayHasKey('created_on', $orderActual['product']);

        if (!$orderExpected->getShopId()->isNull()) {
            $this->assertArrayHasKey('id', $orderActual['shop']);
            $this->assertArrayHasKey('name', $orderActual['shop']);
            $this->assertArrayHasKey('description', $orderActual['shop']);
            $this->assertArrayHasKey('image', $orderActual['shop']);
            $this->assertArrayHasKey('created_on', $orderActual['shop']);

            $this->assertArrayHasKey('price', $orderActual['productShop']);
            $this->assertArrayHasKey('unit', $orderActual['productShop']);
        }

        $this->assertEquals($orderExpected->getId()->getValue(), $orderActual['id']);
        $this->assertEquals($orderExpected->getGroupId()->getValue(), $orderActual['group_id']);
        $this->assertEquals($orderExpected->getListOrdersId()->getValue(), $orderActual['list_orders_id']);
        $this->assertEquals($orderExpected->getUserId()->getValue(), $orderActual['user_id']);
        $this->assertEquals($orderExpected->getDescription()->getValue(), $orderActual['description']);
        $this->assertEquals($orderExpected->getAmount()->getvalue(), $orderActual['amount']);
        $this->assertEquals($orderExpected->getBought(), $orderActual['bought']);

        $product = $orderExpected->getProduct();
        $this->assertEquals($product->getId()->getvalue(), $orderActual['product']['id']);
        $this->assertEquals($product->getName()->getValue(), $orderActual['product']['name']);
        $this->assertEquals($product->getDescription()->getValue(), $orderActual['product']['description']);
        $this->assertEquals($product->getImage()->getValue(), $orderActual['product']['image']);

        if (!$orderExpected->getShopId()->isNull()) {
            $shop = $orderExpected->getShop();
            $this->assertEquals($shop->getId()->getValue(), $orderActual['shop']['id']);
            $this->assertEquals($shop->getName(), $orderActual['shop']['name']);
            $this->assertEquals($shop->getDescription()->getValue(), $orderActual['shop']['description']);
            $this->assertEquals($shop->getImage()->getValue(), $orderActual['shop']['image']);

            /** @var ProductShop[] $productShop */
            $productShop = $orderExpected->getProduct()->getProductShop()->getValues();
            $this->assertEquals($productShop[0]->getPrice()->getValue(), $orderActual['productShop']['price']);
            $this->assertEquals($productShop[0]->getUnit()->getValue(), $orderActual['productShop']['unit']);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdAndOrdersId22(): void
    {
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable(null),
            $this->getOrdersIdentifiers(),
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            true,
            null,
            null
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByIdOrFail')
            ->with($input->groupId, $input->ordersId, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdListOrdersIdAndProductName(): void
    {
        $filterValue = ValueObjectFactory::createNameWithSpaces('Product name');
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable('8da455f5-89e6-43b2-bdef-58e75949c5d2'),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::PRODUCT),
                $filterValue
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $filterValue
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByProductNameFilterOrFail')
            ->with($input->groupId, $input->listOrdersId->toIdentifier(), $input->filterText, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdListOrdersIdAndOrderName(): void
    {
        $filterValue = ValueObjectFactory::createNameWithSpaces('Product name');
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable('8da455f5-89e6-43b2-bdef-58e75949c5d2'),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::ORDER),
                $filterValue
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $filterValue
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByProductNameFilterOrFail')
            ->with($input->groupId, $input->listOrdersId->toIdentifier(), $input->filterText, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdListOrdersIdAndShopName(): void
    {
        $filterValue = ValueObjectFactory::createNameWithSpaces('Shop name');
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable('8da455f5-89e6-43b2-bdef-58e75949c5d2'),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::SHOP),
                $filterValue
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $filterValue
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByShopNameFilterOrFail')
            ->with($input->groupId, $input->listOrdersId->toIdentifier(), $input->filterText, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdListOrdersName(): void
    {
        $filterValue = ValueObjectFactory::createNameWithSpaces('Shop name');
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable('8da455f5-89e6-43b2-bdef-58e75949c5d2'),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::LIST_ORDERS),
                $filterValue
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $filterValue
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersNameOrFail')
            ->with($input->groupId, ValueObjectFactory::createNameWithSpaces($input->filterText->getValue()), $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupIdListOrdersId(): void
    {
        $filterValue = ValueObjectFactory::createNameWithSpaces(null);
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable('8da455f5-89e6-43b2-bdef-58e75949c5d2'),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::LIST_ORDERS),
                $filterValue
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                $filterValue
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByListOrdersIdOrFail')
            ->with($input->listOrdersId->toIdentifier(), $input->groupId, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByGroupIdOrFail');

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldGetOrdersDataByGroupId(): void
    {
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable(null),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            ValueObjectFactory::createFilter(
                'section_filter',
                ValueObjectFactory::createFilterSection(FILTER_SECTION::LIST_ORDERS),
                ValueObjectFactory::createNameWithSpaces(null)
            ),
            ValueObjectFactory::createFilter(
                'text_filter',
                ValueObjectFactory::createFilterDbLikeComparison(FILTER_STRING_COMPARISON::EQUALS),
                ValueObjectFactory::createNameWithSpaces(null)
            )
        );
        $ordersIndexProduct = $this->getOrders();
        $ordersExpectedIndexProduct = $this->getOrdersExpected();

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByGroupIdOrFail')
            ->with($input->groupId, $input->orderAsc)
            ->willReturn($this->ordersPaginator);

        $this->ordersPaginator
            ->expects($this->once())
            ->method('setPagination')
            ->with($input->page->getValue(), $input->pageItems->getValue());

        $this->ordersPaginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($ordersIndexProduct));

        $return = $this->object->__invoke($input);

        foreach ($ordersExpectedIndexProduct as $key => $orderExpected) {
            $this->assertOrderDataIsOk($orderExpected, $return[$key]);
        }
    }

    /** @test */
    public function itShouldFailGettingOrdersDataByGroupIdNotFound(): void
    {
        $input = new OrderGetDataDto(
            ValueObjectFactory::createIdentifier(self::GROUP_ID),
            ValueObjectFactory::createIdentifierNullable(null),
            [],
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(10),
            false,
            null,
            null
        );

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByIdOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByProductNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByShopNameFilterOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersNameOrFail');

        $this->orderRepository
            ->expects($this->never())
            ->method('findOrdersByListOrdersIdOrFail');

        $this->orderRepository
            ->expects($this->once())
            ->method('findOrdersByGroupIdOrFail')
            ->with($input->groupId, $input->orderAsc)
            ->willThrowException(new DBNotFoundException());

        $this->ordersPaginator
            ->expects($this->never())
            ->method('setPagination');

        $this->ordersPaginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
