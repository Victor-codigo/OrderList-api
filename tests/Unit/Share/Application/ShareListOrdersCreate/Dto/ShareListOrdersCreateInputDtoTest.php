<?php

declare(strict_types=1);

namespace Test\Unit\Share\Application\ShareListOrdersCreate\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateInputDto;

class ShareListOrdersCreateInputDtoTest extends TestCase
{
    private const string LIST_ORDERS_ID = 'ba6bed75-4c6e-4ac3-8787-5bded95dac8d';

    private ValidationInterface $validator;
    private MockObject&UserShared $userSession;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ShareListOrdersCreateInputDto($this->userSession, self::LIST_ORDERS_ID);
        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailValidateListOrdersIdIsNull(): void
    {
        $object = new ShareListOrdersCreateInputDto($this->userSession, null);
        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailValidateListOrdersIdIsWrong(): void
    {
        $object = new ShareListOrdersCreateInputDto($this->userSession, 'Wrong id');
        $return = $object->validate($this->validator);

        $this->assertEquals(['list_orders_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
