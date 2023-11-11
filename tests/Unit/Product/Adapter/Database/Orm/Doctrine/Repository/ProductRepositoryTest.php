<?php

declare(strict_types=1);

namespace Test\Unit\Product\Adapter\Database\Orm\Doctrine\Repository;

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
use Product\Adapter\Database\Orm\Doctrine\Repository\ProductRepository;
use Product\Domain\Model\Product;
use Test\Unit\DataBaseTestCase;

class ProductRepositoryTest extends DataBaseTestCase
{
    use ReloadDatabaseTrait;

    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
    private const SHOP_ID = 'e6c1d350-f010-403c-a2d4-3865c14630ec';
    private const PRODUCT_ID = 'afc62bc9-c42c-4c4d-8098-09ce51414a92';
    private const PRODUCT_ID_2 = '8b6d650b-7bb7-4850-bf25-36cda9bce801';
    private const PRODUCT_ID_3 = 'ca10c90a-c7e6-4594-89e9-71d2f5e74710';
    private const PRODUCT_ID_4 = '7e3021d4-2d02-4386-8bbe-887cfe8697a8';
    private const PRODUCT_NAME_EXISTS = 'Perico';

    private ProductRepository $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = $this->entityManager->getRepository(Product::class);
    }

    private function getNewProduct(): Product
    {
        return Product::fromPrimitives(
            'ddb1a26e-ed57-4e83-bc5c-0ee6f6f5c8f8',
            self::GROUP_ID,
            'product 1 name',
            'Product 1 description',
            null
        );
    }

    private function getExistsProduct(): Product
    {
        return Product::fromPrimitives(
            'afc62bc9-c42c-4c4d-8098-09ce51414a92',
            '4b513296-14ac-4fb1-a574-05bc9b1dbe3f',
            'Sabryna Kuhn',
            'Nam tenetur veniam sapiente eum. Explicabo necessitatibus beatae inventore quibusdam. Odit aut non facere facere quisquam alias repellat. Velit vitae sed ut deserunt impedit ea. Id sed eos hic sint reiciendis similique et labore. Velit est perferendis tenetur possimus nobis aliquid soluta. Molestias alias corrupti sequi. Quis sit autem voluptatibus odit voluptate amet. Quibusdam voluptas in quia magni doloribus vel ut. Voluptas et soluta rem aliquam voluptates pariatur et sed.',
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
    public function itShouldSaveTheProductInDatabase(): void
    {
        $productNew = $this->getNewProduct();
        $this->object->save($productNew);

        /** @var Group $groupSaved */
        $productSaved = $this->object->findOneBy(['id' => $productNew->getId()]);

        $this->assertSame($productNew, $productSaved);
    }

    /** @test */
    public function itShouldFailProductIdAlreadyExists()
    {
        $this->expectException(DBUniqueConstraintException::class);

        $this->object->save($this->getExistsProduct());
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
        $this->object->save($this->getNewProduct());
    }

    /** @test */
    public function itShouldRemoveTheProduct(): void
    {
        $product = $this->getExistsProduct();
        $productToRemove = $this->object->findBy(['id' => $product->getId()]);
        $this->object->remove($productToRemove);

        $productRemoved = $this->object->findBy(['id' => $product->getId()]);

        $this->assertEmpty($productRemoved);
    }

    /** @test */
    public function itShouldFailRemovingTheProductErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $product = $this->getNewProduct();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove([$product]);
    }

    /** @test */
    public function itShouldGetTheProductsOfAGroupWhitAName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);

        $this->assertCount(1, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
        }
    }

    /** @test */
    public function itShouldFailGettingTheProductOfAGroupWithANameNoProduct(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('product that not exists');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);
    }

    /** @test */
    public function itShouldGetTheProductsOfAGroupWhitANameAndProductNameStartsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juanola');
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId, $productName, 'Ju');

        $this->assertCount(1, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
        }
    }

    /** @test */
    public function itShouldGetAllProducts(): void
    {
        $productIds = [
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_2),
            ValueObjectFactory::createIdentifier(self::PRODUCT_ID_3),
        ];

        $return = $this->object->findProductsOrFail($productIds);

        $this->assertCount(3, $return);

        foreach ($return as $product) {
            $this->assertContainsEquals($product->getId(), $productIds);
        }
    }

    /** @test */
    public function itShouldGetAllProductsOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findProductsOrFail(null, $groupId);

        $this->assertCount(4, $return);

        foreach ($return as $product) {
            $this->assertEquals($product->getGroupId(), $groupId);
        }
    }

    /** @test */
    public function itShouldGetAllProductsOfAShop(): void
    {
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);

        $return = $this->object->findProductsOrFail(null, null, [$shopId]);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldGetAllProductsOfAGroupAndAShop(): void
    {
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $return = $this->object->findProductsOrFail(null, $groupId, [$shopId]);

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldGetProductsOfAGroupAndWithName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces('Juan Carlos');
        $return = $this->object->findProductsOrFail(null, $groupId, null, $productName);

        $this->assertCount(1, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_2,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldGetAllProductsOfAGroupAndThatStartsWith(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $return = $this->object->findProductsOrFail(null, $groupId, null, null, 'Ju');

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_2,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldGetProductWithName(): void
    {
        $productName = ValueObjectFactory::createNameWithSpaces('Juan Carlos');
        $return = $this->object->findProductsOrFail(null, null, null, $productName);

        $this->assertCount(1, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_2,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldGetAllProductsThatStartsWith(): void
    {
        $return = $this->object->findProductsOrFail(null, null, null, null, 'Ju');

        $this->assertCount(2, $return);

        $expectedProductsId = [
            self::PRODUCT_ID_2,
            self::PRODUCT_ID_4,
        ];

        foreach ($return as $product) {
            $this->assertContains($product->getId()->getValue(), $expectedProductsId);
        }
    }

    /** @test */
    public function itShouldFailGettingAllProductsOfAGroupNotFound(): void
    {
        $shopId = ValueObjectFactory::createIdentifier(self::SHOP_ID);
        $groupId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsOrFail(null, $groupId, [$shopId]);
    }

    /** @test */
    public function itShouldFailGettingAllProductsOfAShopNotFound(): void
    {
        $shopId = ValueObjectFactory::createIdentifier('fc4f5962-02d1-4d80-b3ae-8aeffbcb6268');
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsOrFail(null, $groupId, [$shopId]);
    }

    /** @test */
    public function itShouldFailGettingProductsThereAreNot(): void
    {
        $productId = ValueObjectFactory::createIdentifier('product id');

        $this->expectException(DBNotFoundException::class);
        $this->object->findProductsOrFail([$productId]);
    }
}
