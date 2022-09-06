<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\ValueObject\String;

use Common\Adapter\Validation\Validator;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\ValueObject\String\Identifier;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    private Identifier $object;
    private Validator $validator;
    private string $validId = '77020b89-fb7b-416c-9987-bffbeb3af6f8';
    private string $notValidId = 'not valid id';

    public function setUp(): void
    {
        $this->validator = new Validator();
    }

    private function createIdentifier(string $id)
    {
        return $this->object = new Identifier($id);
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

    public function testUuIdError(): void
    {
        $object = $this->createIdentifier($this->notValidId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], $return,
            'It was expected that validation fail on notNull');
    }
}
