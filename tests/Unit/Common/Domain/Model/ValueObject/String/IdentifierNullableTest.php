<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class IdentifierNullableTest extends TestCase
{
    private ValidationInterface $validator;
    private string $validId = '77020b89-fb7b-416c-9987-bffbeb3af6f8';
    private string $notValidId = 'not valid id';

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createIdentifierNullable(?string $id): IdentifierNullable
    {
        return new IdentifierNullable($id);
    }

    #[Test]
    public function validUuId(): void
    {
        $object = $this->createIdentifierNullable($this->validId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return,
            'It was expected that validation has no errors');
    }

    #[Test]
    public function uuIdValidBlank(): void
    {
        $object = $this->createIdentifierNullable('');
        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return);
    }

    #[Test]
    public function uuIdValidNotNull(): void
    {
        $id = $this->createIdentifierNullable(null);
        $return = $this->validator->validateValueObject($id);

        $this->assertEmpty($return);
    }

    #[Test]
    public function uuIdError(): void
    {
        $object = $this->createIdentifierNullable($this->notValidId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], $return);
    }
}
