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
            '80dcd431-a5f4-4f1b-a49c-59cc9507b915'
        );

        $return = $object->validate($this->validation);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsNull(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            null
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailListOrdersIdIsWrong(): void
    {
        $object = new ShareListOrdersGetDataInputDto(
            $this->userSession,
            'wrong list orders id'
        );

        $return = $object->validate($this->validation);

        $this->assertEquals(['shared_list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
