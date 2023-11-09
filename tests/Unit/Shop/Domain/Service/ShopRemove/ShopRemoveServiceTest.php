<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopRemove\BuiltInFunctionsReturn;
use Shop\Domain\Service\ShopRemove\Dto\ShopRemoveDto;
use Shop\Domain\Service\ShopRemove\ShopRemoveService;

require_once 'tests/BuiltinFunctions/ShopRemoveService.php';

class ShopRemoveServiceTest extends TestCase
{
    private const SHOP_IMAGE_PATH = 'path/to/shop/image';

    private ShopRemoveService $object;
    private MockObject|ShopRepositoryInterface $shopRepository;
    private MockObject|PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShopRemoveService($this->shopRepository, self::SHOP_IMAGE_PATH);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        BuiltInFunctionsReturn::$file_exists = null;
        BuiltInFunctionsReturn::$unlink = null;
    }

    private function getShop(Identifier $shopId, Identifier $groupId, Identifier $productId, string $image = null): Shop
    {
        return new Shop(
            $shopId,
            $groupId,
            ValueObjectFactory::createNameWithSpaces('shop name'),
            ValueObjectFactory::createDescription('shop description'),
            ValueObjectFactory::createPath($image)
        );
    }

    /** @test */
    public function itShouldRemoveAShop(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shop = $this->getShop($shopId, $groupId, $shopId);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with([$shopId], $groupId, [$productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$shop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$shop]));

        $input = new ShopRemoveDto($shopId, $groupId, $productId);

        $return = $this->object->__invoke($input);

        $this->assertEquals($shopId, $return);
    }

    /** @test */
    public function itShouldRemoveAShopImageFileExists(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shop = $this->getShop($shopId, $groupId, $productId, self::SHOP_IMAGE_PATH);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with([$shopId], $groupId, [$productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$shop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$shop]));

        $input = new ShopRemoveDto($shopId, $groupId, $productId);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = true;
        $return = $this->object->__invoke($input);

        $this->assertEquals($shopId, $return);
    }

    /** @test */
    public function itShouldRemoveAShopImageFileNotExists(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productId = ValueObjectFactory::createIdentifier('shop id');
        $shop = $this->getShop($shopId, $groupId, $productId, self::SHOP_IMAGE_PATH);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with([$shopId], $groupId, [$productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->once())
            ->method('remove')
            ->with([$shop]);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$shop]));

        $input = new ShopRemoveDto($shopId, $groupId, $productId);

        $return = $this->object->__invoke($input);

        BuiltInFunctionsReturn::$file_exists = false;
        $this->assertEquals($shopId, $return);
    }

    /** @test */
    public function itShouldFailRemovingAShopProductNotFound(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productId = ValueObjectFactory::createIdentifier('product id');

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with([$shopId], $groupId, [$productId])
            ->willThrowException(new DBNotFoundException());

        $this->shopRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $input = new ShopRemoveDto($shopId, $groupId, $productId);

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    /** @test */
    public function itShouldFailRemovingAShopImageCanNotBeRemoved(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('shop id');
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $productId = ValueObjectFactory::createIdentifier('product id');
        $shop = $this->getShop($shopId, $groupId, $productId, self::SHOP_IMAGE_PATH);

        $this->shopRepository
            ->expects($this->once())
            ->method('findShopsOrFail')
            ->with([$shopId], $groupId, [$productId])
            ->willReturn($this->paginator);

        $this->shopRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$shop]));

        $input = new ShopRemoveDto($shopId, $groupId, $productId);

        BuiltInFunctionsReturn::$file_exists = true;
        BuiltInFunctionsReturn::$unlink = false;
        $this->expectException(DomainInternalErrorException::class);
        $this->object->__invoke($input);
    }
}
