<?php

declare(strict_types=1);

namespace Test\Unit\Notification\Application\NotificationGetData\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use Notification\Application\NotificationGetData\Dto\NotificationGetDataInputDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotificationGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $page = 1;
        $pageItems = 10;
        $lang = 'en';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailPageIsNull(): void
    {
        $page = null;
        $pageItems = 10;
        $lang = 'es';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageIsLowerThanOne(): void
    {
        $page = 0;
        $pageItems = 10;
        $lang = 'en';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsNull(): void
    {
        $page = 1;
        $pageItems = null;
        $lang = 'en';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailPageItemsIsLowerThanOne(): void
    {
        $page = 1;
        $pageItems = 0;
        $lang = 'en';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    /** @test */
    public function itShouldFailPageAndPageItemsIsGreaterThan100(): void
    {
        $page = 1;
        $pageItems = 101;
        $lang = 'en';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::LESS_THAN_OR_EQUAL]], $return);
    }

    /** @test */
    public function itShouldFailLanguageIsWrong(): void
    {
        $page = 1;
        $pageItems = 10;
        $lang = 'ru';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals(['lang' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH]], $return);
    }

    /** @test */
    public function itShouldFailPageAndPageItemsIsLowerThanOne(): void
    {
        $page = 0;
        $pageItems = 0;
        $lang = 'ru';
        $object = new NotificationGetDataInputDto($this->userSession, $page, $pageItems, $lang);

        $return = $object->validate($this->validator);

        $this->assertEquals([
                'page' => [VALIDATION_ERRORS::GREATER_THAN],
                'page_items' => [VALIDATION_ERRORS::GREATER_THAN],
                'lang' => [VALIDATION_ERRORS::CHOICE_NOT_SUCH],
            ],
            $return
        );
    }
}
