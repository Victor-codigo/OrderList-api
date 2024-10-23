<?php

declare(strict_types=1);

namespace Test\Unit\Share\Domain\Service\ShareRecourseRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareRecoursesRemove\Dto\ShareRecoursesRemoveDto;
use Share\Domain\Service\ShareRecoursesRemove\ShareRecoursesRemoveService;

class ShareRecourseRemoveServiceTest extends TestCase
{
    private const string SHARE_ID_EXIST = '72b37f9c-ff55-4581-a131-4270e73012a2';
    private const string SHARE_ID_EXIST_2 = '8aaa96f5-cc54-45cb-bf43-f9b8fe256696';
    private const string SHARE_ID_EXIST_3 = '5552b4a6-8326-462a-a42b-f60b33640aef';
    private const string USER_ID = '8e5b2313-9196-4477-95ca-fe1499200e23';
    private const string LIST_ORDERS_ID = '000d3ba7-fc09-40d6-822e-2d7217e38b46';
    private const string LIST_ORDERS_ID_2 = '8a360f3c-ccd9-4c85-a740-8cf928b4b876';
    private const string LIST_ORDERS_ID_3 = '14febfce-9384-47d5-9560-141f19c84d6c';
    private const string GROUP_ID = 'c72b9092-473b-480a-bd97-08e05f82c951';

    private ShareRecoursesRemoveService $object;
    private MockObject&ShareRepositoryInterface $shareRepository;
    /**
     * @var MockObject&PaginatorInterface<int, Share>
     */
    private MockObject&PaginatorInterface $paginator;
    private \DateTime $sharedDatetimeExpire;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shareRepository = $this->createMock(ShareRepositoryInterface::class);
        $this->paginator = $this->createMock(PaginatorInterface::class);
        $this->object = new ShareRecoursesRemoveService($this->shareRepository);
        $this->sharedDatetimeExpire = new \DateTime();
    }

    /**
     * @return Share[]
     */
    private function getSharedRecurses(): array
    {
        return [
            Share::fromPrimitives(
                self::SHARE_ID_EXIST,
                self::USER_ID,
                self::LIST_ORDERS_ID,
                self::GROUP_ID,
                $this->sharedDatetimeExpire
            ),
            Share::fromPrimitives(
                self::SHARE_ID_EXIST_2,
                self::USER_ID,
                self::LIST_ORDERS_ID_2,
                self::GROUP_ID,
                $this->sharedDatetimeExpire
            ),
            Share::fromPrimitives(
                self::SHARE_ID_EXIST_3,
                self::USER_ID,
                self::LIST_ORDERS_ID_3,
                self::GROUP_ID,
                $this->sharedDatetimeExpire
            ),
        ];
    }

    #[Test]
    public function itShouldRemoveRecourses(): void
    {
        $recoursesId = [
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
        ];
        $sharedRecourses = $this->getSharedRecurses();
        $input = new ShareRecoursesRemoveDto($recoursesId);

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($recoursesId)
            ->willReturn($this->paginator);

        $this->shareRepository
            ->expects($this->once())
            ->method('remove')
            ->with($sharedRecourses);

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, count($recoursesId));

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($sharedRecourses));

        $return = $this->object->__invoke($input);

        $this->assertEquals($recoursesId, $return);
    }

    #[Test]
    public function itShouldFailRemovingRecoursesNotFound(): void
    {
        $recoursesId = [
            ValueObjectFactory::createIdentifier('not found id 1'),
            ValueObjectFactory::createIdentifier('not found id 2'),
        ];
        $input = new ShareRecoursesRemoveDto($recoursesId);

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($recoursesId)
            ->willThrowException(new DBNotFoundException());

        $this->shareRepository
            ->expects($this->never())
            ->method('remove');

        $this->paginator
            ->expects($this->never())
            ->method('setPagination');

        $this->paginator
            ->expects($this->never())
            ->method('getIterator');

        $this->expectException(DBNotFoundException::class);
        $this->object->__invoke($input);
    }

    #[Test]
    public function itShouldFailRemoveRecoursesErrorRemoving(): void
    {
        $recoursesId = [
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_2),
            ValueObjectFactory::createIdentifier(self::SHARE_ID_EXIST_3),
        ];
        $sharedRecourses = $this->getSharedRecurses();
        $input = new ShareRecoursesRemoveDto($recoursesId);

        $this->shareRepository
            ->expects($this->once())
            ->method('findSharedRecursesByIdOrFail')
            ->with($recoursesId)
            ->willReturn($this->paginator);

        $this->shareRepository
            ->expects($this->once())
            ->method('remove')
            ->willThrowException(new DBConnectionException());

        $this->paginator
            ->expects($this->once())
            ->method('setPagination')
            ->with(1, count($recoursesId));

        $this->paginator
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($sharedRecourses));

        $this->expectException(DBConnectionException::class);
        $this->object->__invoke($input);
    }
}
