<?php

declare(strict_types=1);

namespace Test\Unit\Shop\Application\ShopGetFirstLetter\Dto;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Shop\Application\ShopGetFirstLetter\Dto\ShopGetFirstLetterInputDto;

class ShopGetFirstLetterInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $object = new ShopGetFirstLetterInputDto('4b513296-14ac-4fb1-a574-05bc9b1dbe3f');

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ShopGetFirstLetterInputDto(null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    /** @test */
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ShopGetFirstLetterInputDto('wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
