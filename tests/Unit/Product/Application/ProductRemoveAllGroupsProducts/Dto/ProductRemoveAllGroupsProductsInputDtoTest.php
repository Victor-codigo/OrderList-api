<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductRemoveAllGroupsProducts\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Security\UserShared;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsInputDto;

class ProductRemoveAllGroupsProductsInputDtoTest extends TestCase
{
    private ValidationInterface $validator;
    private MockObject|UserShared $userSession;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userSession = $this->createMock(UserShared::class);
        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ProductRemoveAllGroupsProductsInputDto(
            $this->userSession,
            [
                '93bbcdff-0536-447b-a250-ccbfc18b7a19',
                'e8d9727c-0af5-4cd5-9143-1bb51eb7fc3b',
            ],
            'SystemKey'
        );

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupsIdAreEmpty(): void
    {
        $object = new ProductRemoveAllGroupsProductsInputDto(
            $this->userSession,
            [],
            'SystemKey'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id_empty' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }

    #[Test]
    public function itShouldFailGroupsIdAreWrong(): void
    {
        $object = new ProductRemoveAllGroupsProductsInputDto(
            $this->userSession,
            [
                '93bbcdff-0536-447b-a250-ccbfc18b7a19',
                'e8d9727c-0af5-4cd5-9143-1bb51eb7fc3b',
                'wrong id',
            ],
            'SystemKey'
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['groups_id' => [[VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]]], $return);
    }

    #[Test]
    public function itShouldFailSystemKeyIsEmpty(): void
    {
        $object = new ProductRemoveAllGroupsProductsInputDto(
            $this->userSession,
            [
                '93bbcdff-0536-447b-a250-ccbfc18b7a19',
                'e8d9727c-0af5-4cd5-9143-1bb51eb7fc3b',
            ],
            ''
        );

        $return = $object->validate($this->validator);

        $this->assertEquals(['system_key' => [VALIDATION_ERRORS::NOT_BLANK]], $return);
    }
}
