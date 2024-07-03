<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    private ValidationInterface $validator;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createUrl(?string $url): Url
    {
        return new Url($url);
    }

    /** @test */
    public function urlWithHttpOk(): void
    {
        $url = $this->createUrl('http://www.domain.com');
        $return = $this->validator->validateValueObject($url);

        $this->assertEmpty($return);
    }

    /** @test */
    public function urlWithHttpsOk(): void
    {
        $url = $this->createUrl('https://www.domain.com');
        $return = $this->validator->validateValueObject($url);

        $this->assertEmpty($return);
    }

    /** @test */
    public function urlNotBlank(): void
    {
        $email = $this->createUrl('');
        $return = $this->validator->validateValueObject($email);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK], $return);
    }

    /** @test */
    public function urlNotNull(): void
    {
        $url = $this->createUrl(null);
        $return = $this->validator->validateValueObject($url);

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    /** @test */
    public function urlNotValid(): void
    {
        $url = $this->createUrl('www.domain.com');
        $return = $this->validator->validateValueObject($url);

        $this->assertEquals([VALIDATION_ERRORS::URL], $return);
    }
}
