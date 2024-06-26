<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Address;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    private ValidationInterface $validation;

    private const VALID_ADDRESS = 'C\ Cristóbal, 10. #3-b';

    public function setUp(): void
    {
        parent::setUp();

        $this->validation = new ValidationChain();
    }

    private function createAddress(?string $address): Address
    {
        return new Address($address);
    }

    /** @test */
    public function itShouldValidate(): void
    {
        $address = $this->createAddress(self::VALID_ADDRESS);
        $return = $this->validation->validateValueObject($address);

        $this->assertEmpty($return);
    }

    /** @test */
    public function itShouldFail(): void
    {
        $address = $this->createAddress('C\ igual=');
        $return = $this->validation->validateValueObject($address);

        $this->assertEquals([VALIDATION_ERRORS::REGEX_FAIL], $return);
    }

    /** @test */
    public function itShouldFailLessThan5Characters(): void
    {
        $address = $this->createAddress('C\ g');
        $return = $this->validation->validateValueObject($address);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return);
    }

    /** @test */
    public function itShouldFailMoreThan100Characters(): void
    {
        $address = $this->createAddress(str_pad('', 101, 'p'));
        $return = $this->validation->validateValueObject($address);

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_LONG], $return);
    }
}
