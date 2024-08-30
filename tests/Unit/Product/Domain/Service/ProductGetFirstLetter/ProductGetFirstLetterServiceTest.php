<?php

declare(strict_types=1);

namespace Test\Unit\Product\Domain\Service\ProductGetFirstLetter;

use PHPUnit\Framework\Attributes\Test;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetFirstLetter\Dto\ProductGetFirstLetterDto;
use Product\Domain\Service\ProductGetFirstLetter\ProductGetFirstLetterService;

class ProductGetFirstLetterServiceTest extends TestCase
{
    private ProductGetFirstLetterService $object;
    private MockObject|ProductRepositoryInterface $productRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->object = new ProductGetFirstLetterService($this->productRepository);
    }

    #[Test]
    public function itShouldGetProductsFirstLetterOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $expected = ['a', 'b', 'c'];
        $input = new ProductGetFirstLetterDto($groupId);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupProductsFirstLetterOrFail')
            ->with($input->groupId)
            ->willReturn($expected);

        $return = $this->object->__invoke($input);

        $this->assertSame($expected, $return);
    }

    #[Test]
    public function itShouldFailGetProductsFirstLetterOfAGroupNoProducts(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $input = new ProductGetFirstLetterDto($groupId);

        $this->productRepository
            ->expects($this->once())
            ->method('findGroupProductsFirstLetterOrFail')
            ->with($input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
