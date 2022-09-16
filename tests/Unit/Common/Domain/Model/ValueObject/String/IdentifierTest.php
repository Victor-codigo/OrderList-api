<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class IdentifierTest extends TestCase
{
    private ValidationInterface $validator;
    private string $validId = '77020b89-fb7b-416c-9987-bffbeb3af6f8';
    private string $notValidId = 'not valid id';

    public function setUp(): void
    {
        $this->validator = new ValidationChain();
    }

    private function createIdentifier(string $id): Identifier
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

    public function testUuIdError(): void
    {
        $object = $this->createIdentifier($this->notValidId);
        $return = $this->validator->validateValueObject($object);

        $this->assertEquals([VALIDATION_ERRORS::UUID_INVALID_CHARACTERS], $return,
            'It was expected that validation fail on notNull');
    }
}
