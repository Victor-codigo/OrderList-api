<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Jwt;

use Common\Adapter\Jwt\JwtFirebaseHS256Adapter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JwtFirebaseHS256AdapterTest extends TestCase
{
    private const SECRET_KEY = 'this is a secret key';
    private const PAYLOAD = [
        'param1' => 1,
        'param2' => 'two',
        'param3' => true,
    ];

    private MockObject|JwtFirebaseHS256Adapter $object;
    private Key $secretKey;

    public function setUp(): void
    {
        $this->markTestSkipped('Intall library [composer require firebase/php-jwt]');

        parent::setUp();

        $this->secretKey = new Key(self::SECRET_KEY, JwtFirebaseHS256Adapter::ALGORITM);
        $this->object = $this->getMockBuilder(JwtFirebaseHS256Adapter::class)
            ->setConstructorArgs([self::SECRET_KEY])
            ->onlyMethods(['getDateTime'])
            ->getMock();
    }

    /** @test */
    public function itShouldEncodeWitoutExpirationTime(): void
    {
        $return = $this->object->encode(self::PAYLOAD);
        $tokenDecoded = JWT::decode($return, $this->secretKey);

        $this->assertObjectNotHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        $this->assertObjectHasAttribute('param1', $tokenDecoded);
        $this->assertObjectHasAttribute('param2', $tokenDecoded);
        $this->assertObjectHasAttribute('param3', $tokenDecoded);
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    /** @test */
    public function itShouldEncodeWitExpirationTimeAt3600Seconds(): void
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeTokenExpiration = (new \DateTime())->add(new \DateInterval("PT{$expirationInSeconds}S"));

        $this->object
            ->expects($this->once())
            ->method('getDateTime')
            ->with(null)
            ->willReturn($dateTimeTokenExpiration);

        $return = $this->object->encode(self::PAYLOAD, $expirationInSeconds);   // 24H
        $tokenDecoded = JWT::decode($return, $this->secretKey);

        $this->assertObjectHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        $this->assertObjectHasAttribute('expire', $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA});
        $this->assertEquals($dateTimeTokenExpiration->getTimestamp(), $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}->expire);
        $this->assertObjectHasAttribute('param1', $tokenDecoded);
        $this->assertObjectHasAttribute('param2', $tokenDecoded);
        $this->assertObjectHasAttribute('param3', $tokenDecoded);
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    /* @test */
    public function itShouldDecodedTheToken(): void
    {
        $token = JWT::encode(self::PAYLOAD, self::SECRET_KEY, JwtFirebaseHS256Adapter::ALGORITM);
        $tokenDecoded = $this->object->decode($token);

        $this->assertObjectHasAttribute('param1', $tokenDecoded);
        $this->assertObjectHasAttribute('param2', $tokenDecoded);
        $this->assertObjectHasAttribute('param3', $tokenDecoded);
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    /** @test */
    public function itShouldNotBeExpiredNotAttributeExpired()
    {
        $token = $this->object->encode(self::PAYLOAD);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        if (isset($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA})) {
            $this->assertObjectNotHasAttribute('expire', $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA});
        }

        if (!isset($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA})) {
            $this->assertObjectNotHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        }

        $this->assertFalse($hasExpired);
    }

    /** @test */
    public function itShouldNotBeExpiredTimeToExpireIsTheSameAsTimeNow()
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));

        $this->object
            ->expects($this->exactly(3))
            ->method('getDateTime')
            ->withConsecutive([null], [$dateTimeTokenExpiration->getTimestamp()], [null])
            ->willReturnOnConsecutiveCalls($dateTimeNow, $dateTimeTokenExpiration, $dateTimeTokenExpiration);

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertObjectHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        $this->assertObjectHasAttribute('expire', $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA});
        $this->assertFalse($hasExpired);
    }

    /** @test */
    public function itShouldNotBeExpiredTimeToExpireIsHigherThanTimeNow()
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));
        $intervalLessOnesecond = new \DateInterval('PT1S');
        $intervalLessOnesecond->invert = true;
        $dateTimeTokenexpirationLessOne = (clone $dateTimeTokenExpiration)->add($intervalLessOnesecond);

        $this->object
            ->expects($this->exactly(3))
            ->method('getDateTime')
            ->withConsecutive([null], [$dateTimeTokenExpiration->getTimestamp()], [null])
            ->willReturnOnConsecutiveCalls($dateTimeNow, $dateTimeTokenExpiration, $dateTimeTokenexpirationLessOne);

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertObjectHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        $this->assertObjectHasAttribute('expire', $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA});
        $this->assertFalse($hasExpired);
    }

    /** @test */
    public function itShouldBeExpiredTimeToExpireIsLowerThanTimeNow()
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));
        $dateTimeTokenexpirationPlusOne = (clone $dateTimeTokenExpiration)->add(new \DateInterval('PT1S'));

        $this->object
            ->expects($this->exactly(3))
            ->method('getDateTime')
            ->withConsecutive([null], [$dateTimeTokenExpiration->getTimestamp()], [null])
            ->willReturnOnConsecutiveCalls($dateTimeNow, $dateTimeTokenExpiration, $dateTimeTokenexpirationPlusOne);

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertObjectHasAttribute(JwtFirebaseHS256Adapter::KEY_TOKEN_DATA, $tokenDecoded);
        $this->assertObjectHasAttribute('expire', $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA});
        $this->assertTrue($hasExpired);
    }
}
