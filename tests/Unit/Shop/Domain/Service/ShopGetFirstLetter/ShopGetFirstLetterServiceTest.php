<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Domain\Service\ShopGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;
use Shop\Domain\Service\ShopGetFirstLetter\Dto\ShopGetFirstLetterDto;
use Shop\Domain\Service\ShopGetFirstLetter\ShopGetFirstLetterService;

class ShopGetFirstLetterServiceTest extends TestCase
{
    private ShopGetFirstLetterService $object;
    private MockObject&ShopRepositoryInterface $shopRepository;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->shopRepository = $this->createMock(ShopRepositoryInterface::class);
        $this->object = new ShopGetFirstLetterService($this->shopRepository);
    }

    #[Test]
    public function itShouldGetShopsFirstLetterOfAGroup(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $expected = ['a', 'b', 'c'];
        $input = new ShopGetFirstLetterDto($groupId);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupShopsFirstLetterOrFail')
            ->with($input->groupId)
            ->willReturn($expected);

        $return = $this->object->__invoke($input);

        $this->assertSame($expected, $return);
    }

    #[Test]
    public function itShouldFailGetShopsFirstLetterOfAGroupNoShops(): void
    {
        $groupId = ValueObjectFactory::createIdentifier('group id');
        $input = new ShopGetFirstLetterDto($groupId);

        $this->shopRepository
            ->expects($this->once())
            ->method('findGroupShopsFirstLetterOrFail')
            ->with($input->groupId)
            ->willThrowException(new DBNotFoundException());

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }
}
