<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Product\Domain\Model\Product;

class ProductTest extends TestCase
{
    #[Test]
    public function itShouldCreateAProductFromArray(): void
    {
        $productData = [
            'id' => 'product id',
            'group_id' => 'group_id',
            'name' => 'product name',
            'description' => 'product description',
            'image' => 'product image',
        ];

        $return = Product::fromPrimitiveArrayOfData($productData);

        $this->assertEquals($productData['id'], $return->getId()->getValue());
        $this->assertEquals($productData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($productData['name'], $return->getName()->getValue());
        $this->assertEquals($productData['description'], $return->getDescription()->getValue());
        $this->assertEquals($productData['image'], $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAProductFromArrayDescriptionNotExists(): void
    {
        $productData = [
            'id' => 'product id',
            'group_id' => 'group_id',
            'name' => 'product name',
            'image' => 'product image',
        ];

        $return = Product::fromPrimitiveArrayOfData($productData);

        $this->assertEquals($productData['id'], $return->getId()->getValue());
        $this->assertEquals($productData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($productData['name'], $return->getName()->getValue());
        $this->assertEquals(null, $return->getDescription()->getValue());
        $this->assertEquals($productData['image'], $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldCreateAProductFromArrayImageNotExists(): void
    {
        $productData = [
            'id' => 'product id',
            'group_id' => 'group_id',
            'name' => 'product name',
            'description' => 'product description',
        ];

        $return = Product::fromPrimitiveArrayOfData($productData);

        $this->assertEquals($productData['id'], $return->getId()->getValue());
        $this->assertEquals($productData['group_id'], $return->getGroupId()->getValue());
        $this->assertEquals($productData['name'], $return->getName()->getValue());
        $this->assertEquals($productData['description'], $return->getDescription()->getValue());
        $this->assertEquals(null, $return->getImage()->getValue());
    }

    #[Test]
    public function itShouldFailCreatingAProductFromArrayIdNotExists(): void
    {
        $productData = [
            'group_id' => 'group_id',
            'name' => 'product name',
            'description' => 'product description',
            'image' => 'product image',
        ];

        $this->expectException(LogicException::class);
        Product::fromPrimitiveArrayOfData($productData);
    }

    #[Test]
    public function itShouldFailCreatingAProductFromArrayGroupIdNotExists(): void
    {
        $productData = [
            'id' => 'product id',
            'name' => 'product name',
            'description' => 'product description',
            'image' => 'product image',
        ];

        $this->expectException(LogicException::class);
        Product::fromPrimitiveArrayOfData($productData);
    }

    #[Test]
    public function itShouldFailCreatingAProductFromArrayNameNotExists(): void
    {
        $productData = [
            'id' => 'product id',
            'group_id' => 'group_id',
            'description' => 'product description',
            'image' => 'product image',
        ];

        $this->expectException(LogicException::class);
        Product::fromPrimitiveArrayOfData($productData);
    }
}
