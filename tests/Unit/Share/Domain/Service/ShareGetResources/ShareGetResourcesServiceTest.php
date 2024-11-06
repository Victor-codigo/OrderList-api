<?php

declare(strict_types=1);

namespace Test\Unit\Share\Domain\Service\ShareGetResources;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Model\ListOrders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareGetResources\Dto\ShareGetResourcesDto;
use Share\Domain\Service\ShareGetResources\ShareGetResourcesService;

class ShareGetResourcesServiceTest extends TestCase
{
    private const array SHARE_IDS = [
        '72b37f9c-ff55-4581-a131-4270e73012a2',
        '8aaa96f5-cc54-45cb-bf43-f9b8fe256696',
        '5552b4a6-8326-462a-a42b-f60b33640aef',
    ];
    private const string USER_ID = '2606508b-4516-45d6-93a6-c7cb416b7f3';
    private const string LIST_ORDERS_ID = 'ccea1681-1e76-4b3b-bac8-dffce304c97d';
    private const string GROUP_ID = '5ae14a5f-2e36-4521-b1c1-2b4a8f38b05e';

    private ShareGetResourcesService $object;
    private MockObject&ShareRepositoryInterface $shareRepository;
    /**
     * @var MockObject&PaginatorInterface<int, Share>
     */
    private MockObject&PaginatorInterface $paginator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shareRepository = $this->createMock(ShareRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShareGetResourcesService($this->shareRepository);
    }

    /**
     * @param string[] $ids
     *
     * @return Identifier[]
     */
    private function getSharedIdsAsIdentifier(array $ids): array
    {
        return array_map(
            fn (string $shareId): Identifier => ValueObjectFactory::createIdentifier($shareId),
            $ids
        );
    }

    /**
     * @return Share[]
     */
    private function getShareResources(): array
    {
        /** @var MockObject&ListOrders $listOrders1 */
        $listOrders1 = $this->createMock(ListOrders::class);
        /** @var MockObject&ListOrders $listOrders2 */
        $listOrders2 = $this->createMock(ListOrders::class);
        /** @var MockObject&ListOrders $listOrders3 */
        $listOrders3 = $this->createMock(ListOrders::class);

        return [
            Share::fromPrimitives(
                self::SHARE_IDS[0],
                self::USER_ID,
                self::LIST_ORDERS_ID,
                self::GROUP_ID,
                new \DateTime('2024-10-15 12:00:00')
            ),
            Share::fromPrimitives(
                self::SHARE_IDS[1],
                self::USER_ID,
                self::LIST_ORDERS_ID,
                self::GROUP_ID,
                new \DateTime('2024-10-16 12:00:00')
            ),
            Share::fromPrimitives(
                self::SHARE_IDS[2],
                self::USER_ID,
                self::LIST_ORDERS_ID,
                self::GROUP_ID,
                new \DateTime('2024-10-17 12:00:00')
            ),
        ];
    }

    #[Test]
    public function itShouldGetAResource(): void
    {
        $input = new ShareGetResourcesDto($this->getSharedIdsAsIdentifier(self::SHARE_IDS));
        $shareResources = $this->getShareResources();

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($input->resourcesId)
            ->willReturn($this->paginator);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, 3);

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($shareResources));

        $return = $this->object->__invoke($input);

        $this->assertEquals($shareResources, $return);
    }

    #[Test]
    public function itShouldFailResourcesIdEmpty(): void
    {
        $input = new ShareGetResourcesDto([]);

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($input->resourcesId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailResourcesIdNotFound(): void
    {
        $input = new ShareGetResourcesDto([
            ValueObjectFactory::createIdentifier('shared id 1'),
            ValueObjectFactory::createIdentifier('shared id 2'),
        ]);

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($input->resourcesId)
            ->willThrowException(new DBNotFoundException());

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $return = $this->object->__invoke($input);

        $this->assertEmpty($return);
    }
}
