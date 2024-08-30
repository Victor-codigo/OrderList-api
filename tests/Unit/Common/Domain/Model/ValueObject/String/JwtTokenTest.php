<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Model\ValueObject\String;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Jwt\JwtLexikAdapter;
use Common\Adapter\Validation\ValidationChain;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;
use PHPUnit\Framework\TestCase;

class JwtTokenTest extends TestCase
{
    private ValidationInterface $validator;
    private const string PATH_PRIVATE_KEY = 'src/Common/Adapter/Framework/Config/JwtKeys/Lexik/private.pem';

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->validator = new ValidationChain();
    }

    private function createToken(array $data = []): JwtToken
    {
        $encoder = new JwtLexikAdapter(file_get_contents(self::PATH_PRIVATE_KEY));
        $token = $encoder->encode($data);

        return new JwtToken($token);
    }

    #[Test]
    public function tokenOk(): void
    {
        $token = $this->createToken();
        $return = $this->validator->validateValueObject($token);

        $this->assertEmpty($return);
    }

    #[Test]
    public function tokenNotBlank(): void
    {
        $return = $this->validator->validateValueObject(new JwtToken(''));

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::STRING_TOO_SHORT], $return);
    }

    #[Test]
    public function tokenNotNull(): void
    {
        $return = $this->validator->validateValueObject(new JwtToken(null));

        $this->assertEquals([VALIDATION_ERRORS::NOT_BLANK, VALIDATION_ERRORS::NOT_NULL], $return);
    }

    #[Test]
    public function tokenTooShort(): void
    {
        $return = $this->validator->validateValueObject(new JwtToken(str_pad('', 35, '-')));

        $this->assertEquals([VALIDATION_ERRORS::STRING_TOO_SHORT], $return);
    }
}
