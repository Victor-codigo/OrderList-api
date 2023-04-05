<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\NotificationGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationGetData\Dto\NotificationGetDataInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use User\Domain\Model\User;

class NotificationGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|User $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(User::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $page = 1;
        $pageItems = 10;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $page = null;
        $pageItems = 10;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsLowerThanOne(): void
    {
        $page = 0;
        $pageItems = 10;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $page = 1;
        $pageItems = null;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsLowerThanOne(): void
    {
        $page = 1;
        $pageItems = 0;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageAndPageItemsIsGreaterThan100(): void
    {
        $page = 1;
        $pageItems = 101;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::LESS_THAN_OR_EQUAL]], $return);
    }

    /** @test */
    public function itShouldFailPageAndPageItemsIsLowerThanOne(): void
    {
        $page = 0;
        $pageItems = 0;
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'page' => [VALIDATION_ERRORS::GREATER_THAN],
                'page_items' => [VALIDATION_ERRORS::GREATER_THAN],
            ],
            $return
        );
    }
}
