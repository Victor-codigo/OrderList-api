<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Jwt;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\JwtLexikAdapter;
use PHPUnit\Framework\TestCase;

class JwtLexikAdapterTest extends TestCase
{
    private const string TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImxvbGFpbG8iLCJpYXQiOjE2Njc2NDM1NDQsImV4cCI6MTY2NzY0NzE0NH0.ThN7W_5WGhq7AzO7Jfz9GvgGrG_YJVbalwa5qDxJkfU';

    private JwtLexikAdapter $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new JwtLexikAdapter('33');
    }

    /** @test */
    public function itShouldEncodeTheDataInTheTokenCorrectly(): void
    {
        $data = [
            'username' => 'lolailo',
            'other' => 'other',
        ];

        $return = $this->object->encode($data, 3600);
        $tokenDecoded = $this->object->decode($return);

        $this->assertTrue(property_exists($tokenDecoded, 'iat'));
        $this->assertTrue(property_exists($tokenDecoded, 'exp'));
        $this->assertTrue(property_exists($tokenDecoded, 'username'));
        $this->assertTrue(property_exists($tokenDecoded, 'other'));

        $this->assertSame($data['username'], $tokenDecoded->username);
        $this->assertSame($data['other'], $tokenDecoded->other);
        $this->assertIsInt($tokenDecoded->iat);
        $this->assertIsInt($tokenDecoded->exp);
        $this->assertGreaterThanOrEqual($tokenDecoded->iat, $tokenDecoded->exp);
    }

    /** @test */
    public function itShouldEncodeTheDataInTheTokenExpirationIsZero(): void
    {
        $data = [
            'username' => 'lolailo',
            'other' => 'other',
        ];

        $return = $this->object->encode($data, 0);
        $tokenDecoded = $this->object->decode($return);

        $this->assertTrue(property_exists($tokenDecoded, 'iat'));
        $this->assertTrue(property_exists($tokenDecoded, 'exp'));
        $this->assertTrue(property_exists($tokenDecoded, 'username'));
        $this->assertTrue(property_exists($tokenDecoded, 'other'));

        $this->assertSame($data['username'], $tokenDecoded->username);
        $this->assertSame($data['other'], $tokenDecoded->other);
        $this->assertIsInt($tokenDecoded->iat);
        $this->assertIsInt($tokenDecoded->exp);
        $this->assertGreaterThanOrEqual($tokenDecoded->iat, $tokenDecoded->exp);
    }

    /** @test */
    public function itShouldDecodeTheTokenCorrectly(): void
    {
        $expect = [
            'username' => 'lolailo',
            'iat' => 1667643544,
            'exp' => 1667647144,
        ];

        $return = $this->object->decode(self::TOKEN);

        $this->assertTrue(property_exists($return, 'iat'));
        $this->assertTrue(property_exists($return, 'exp'));
        $this->assertTrue(property_exists($return, 'username'));

        $this->assertSame($expect, (array) $return);
    }

    /** @test */
    public function itShouldFailWrongToken(): void
    {
        $this->expectException(JwtException::class);

        $return = $this->object->decode(self::TOKEN.'-wrong token');
    }

    /** @test */
    public function itShouldNotBeExpired(): void
    {
        $tokenDecoded = new \stdClass();
        $tokenDecoded->iat = (new \DateTimeImmutable())->getTimestamp();
        $tokenDecoded->exp = $tokenDecoded->iat + 3600;

        $return = $this->object->hasExpired($tokenDecoded);

        $this->assertFalse($return);
    }

    /** @test */
    public function itShouldBeExpired(): void
    {
        $tokenDecoded = new \stdClass();
        $tokenDecoded->iat = (new \DateTimeImmutable())->getTimestamp();
        $tokenDecoded->exp = $tokenDecoded->iat - 1;

        $return = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldBeExpiredBadFormedNotIatAttribute(): void
    {
        $tokenDecoded = new \stdClass();
        $tokenDecoded->exp = 33;
        $tokenDecoded->username = 'lolailo';
        $return = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldBeExpiredBadFormedNotExpAttribute(): void
    {
        $tokenDecoded = new \stdClass();
        $tokenDecoded->iat = 33;
        $tokenDecoded->username = 'lolailo';
        $return = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue($return);
    }

    /** @test */
    public function itShouldBeExpiredBadFormedNotAitAndExpAttribute(): void
    {
        $tokenDecoded = new \stdClass();
        $tokenDecoded->username = 'lolailo';
        $return = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue($return);
    }
}
