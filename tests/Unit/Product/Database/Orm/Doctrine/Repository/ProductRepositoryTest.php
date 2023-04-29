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
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Product\Adapter\Database\Orm\Doctrine\Repository\ProductRepository;
use Product\Domain\Model\Product;
use Test\Unit\DataBaseTestCase;

class ProductRepositoryTest extends DataBaseTestCase
{
    use RefreshDatabaseTrait;

    private const GROUP_ID = '4b513296-14ac-4fb1-a574-05bc9b1dbe3f';
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
        $this->object->remove($productToRemove[0]);

        $productRemoved = $this->object->findBy(['id' => $product->getId()]);

        $this->assertEmpty($productRemoved);
    }

    /** @test */
    public function itShouldFailRemovingTheProductErrorConnection(): void
    {
        $this->expectException(DBConnectionException::class);

        $group = $this->getNewProduct();

        /** @var MockObject|ObjectManager $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock
            ->expects($this->once())
            ->method('flush')
            ->willThrowException(ConnectionException::driverRequired(''));

        $this->mockObjectManager($this->object, $objectManagerMock);
        $this->object->remove($group);
    }

    /** @test */
    public function itShouldGetTheProductsOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId);

        $this->assertCount(3, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
        }
    }

    /** @test */
    public function itShouldGetTheProductOfAGroupWithAName(): void
    {
        $groupId = ValueObjectFactory::createIdentifier(self::GROUP_ID);
        $productName = ValueObjectFactory::createNameWithSpaces(self::PRODUCT_NAME_EXISTS);
        $return = $this->object->findProductsByGroupAndNameOrFail($groupId, $productName);

        $this->assertCount(1, $return);

        foreach ($return as $product) {
            $this->assertEquals($groupId, $product->getGroupId());
            $this->assertEquals($productName, $product->getName());
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
}
