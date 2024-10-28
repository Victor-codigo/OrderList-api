<?php

declare(strict_types=1);

namespace Test\Unit\Share\Application\ShareGetResources\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataInputDto;

class ShareListOrdersGetDataInputDtoTest extends TestCase
{
    private ValidationInterface $validation;
    private MockObject&UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validation = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            10
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            null,
            1,
            10
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            'wrong list orders id',
            1,
            10
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }

    #[Test]
    public function itShouldFailPageIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            null,
            10
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailPageIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            -1,
            10
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }

    #[Test]
    public function itShouldFailPageItemsIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            null
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailPageItemsIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915',
            1,
            -1
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['page_items' => [VALIDATION_ERRORS::GREATER_THAN]], $return);
    }
}
