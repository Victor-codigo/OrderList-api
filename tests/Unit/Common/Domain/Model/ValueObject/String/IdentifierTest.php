<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    private ValidationInterface $validator;
    private string $validId = '77020b89-fb7b-416c-9987-bffbeb3af6f8';
    private string $notValidId = 'not valid id';

    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createIdentifier(string|null $id): Identifier
    {
        return new Identifier($id);
    }

    public function testValidUuId(): void
    {
        $object = $this->createIdentifier($this->validId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEmpty($return,
            'It was expected that validation has no errors');
    }

    public function testUuIdNotBlank(): void
    {
        $object = $this->createIdentifier('');
        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return,
            'It was expected that validation fail on notBlank');
    }

    public function testUuIdNotNull(): void
    {
        $id = $this->createIdentifier(null);
        $return = $this->validator->validateValueObject($id);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    public function testUuIdError(): void
    {
        $object = $this->createIdentifier($this->notValidId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], $return,
            'It was expected that validation fail on notNull');
    }

    public function testToString(): void
    {
        $object = $this->createIdentifier($this->validId);
        $return = $object->__toString();

        $this->assertEquals($this->validId, $return);
    }

    public function testToStringOnValueNull(): void
    {
        $object = $this->createIdentifier(null);
        $return = $object->__toString();

        $this->assertEquals('', $return);
    }
}
