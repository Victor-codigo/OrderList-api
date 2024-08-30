<?php

declare(strict_types=1);

namespace Test\Unit\Product\Application\ProductGetFirstLetter\Dto;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;
use Product\Application\ProductGetFirstLetter\Dto\ProductGetFirstLetterInputDto;

class ProductGetFirstLetterInputDtoTest extends TestCase
{
    private ValidationInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    #[Test]
    public function itShouldValidate(): void
    {
        $object = new ProductGetFirstLetterInputDto('4b513296-14ac-4fb1-a574-05bc9b1dbe3f');

        $return = $object->validate($this->validator);

        $this->assertEmpty($return);
    }

    #[Test]
    public function itShouldFailGroupIdIsNull(): void
    {
        $object = new ProductGetFirstLetterInputDto(null);

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL]], $return);
    }

    #[Test]
    public function itShouldFailGroupIdIsWrong(): void
    {
        $object = new ProductGetFirstLetterInputDto('wrong id');

        $return = $object->validate($this->validator);

        $this->assertEquals(['group_id' => [VALIDATION_ERRORS::UUID_INVALID_CHARACTERS]], $return);
    }
}
