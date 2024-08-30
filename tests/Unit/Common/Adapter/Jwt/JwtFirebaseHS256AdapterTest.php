<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Jwt;

use PHPUnit\Framework\Attributes\Test;
use Common\Adapter\Jwt\JwtFirebaseHS256Adapter;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JwtFirebaseHS256AdapterTest extends TestCase
{
    private const string SECRET_KEY = 'this is a secret key';
    private const array PAYLOAD = [
        'param1' => 1,
        'param2' => 'two',
        'param3' => true,
    ];

    private MockObject|JwtFirebaseHS256Adapter $object;
    private Key $secretKey;

    #[\Override]
    public function setUp(): void
    {
        $this->markTestSkipped('Install library [composer require firebase/php-jwt]');

        parent::setUp();

        $this->secretKey = new Key(self::SECRET_KEY, JwtFirebaseHS256Adapter::ALGORITHM);
        $this->object = $this->getMockBuilder(JwtFirebaseHS256Adapter::class)
            ->setConstructorArgs([self::SECRET_KEY])
            ->onlyMethods(['getDateTime'])
            ->getMock();
    }

    #[Test]
    public function itShouldEncodeWithoutExpirationTime(): void
    {
        $return = $this->object->encode(self::PAYLOAD);
        $tokenDecoded = JWT::decode($return, $this->secretKey);

        $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        $this->assertTrue(property_exists($tokenDecoded, 'params1'));
        $this->assertTrue(property_exists($tokenDecoded, 'params2'));
        $this->assertTrue(property_exists($tokenDecoded, 'params3'));
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    #[Test]
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

        $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        $this->assertTrue(property_exists($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}, 'expire'));
        $this->assertEquals($dateTimeTokenExpiration->getTimestamp(), $tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}->expire);
        $this->assertTrue(property_exists($tokenDecoded, 'params1'));
        $this->assertTrue(property_exists($tokenDecoded, 'params2'));
        $this->assertTrue(property_exists($tokenDecoded, 'params3'));
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    #[Test]
    public function itShouldDecodedTheToken(): void
    {
        $token = JWT::encode(self::PAYLOAD, self::SECRET_KEY, JwtFirebaseHS256Adapter::ALGORITHM);
        $tokenDecoded = $this->object->decode($token);

        $this->assertTrue(property_exists($tokenDecoded, 'params1'));
        $this->assertTrue(property_exists($tokenDecoded, 'params2'));
        $this->assertTrue(property_exists($tokenDecoded, 'params3'));
        $this->assertEquals(self::PAYLOAD['param1'], $tokenDecoded->param1);
        $this->assertEquals(self::PAYLOAD['param2'], $tokenDecoded->param2);
        $this->assertEquals(self::PAYLOAD['param3'], $tokenDecoded->param3);
    }

    #[Test]
    public function itShouldNotBeExpiredNotAttributeExpired(): void
    {
        $token = $this->object->encode(self::PAYLOAD);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        if (isset($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA})) {
            $this->assertTrue(property_exists($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}, 'expire'));
        }

        if (!isset($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA})) {
            $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        }

        $this->assertFalse($hasExpired);
    }

    #[Test]
    public function itShouldNotBeExpiredTimeToExpireIsTheSameAsTimeNow(): void
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));

        $matcher = $this->exactly(3);
        $this->object
            ->expects($matcher)
            ->method('getDateTime')
            ->willReturnCallback(function (?float $timestamp) use ($matcher, $dateTimeTokenExpiration, $dateTimeNow) {
                $expectedNumCall = $matcher->numberOfInvocations();

                return match ([$expectedNumCall, $timestamp]) {
                    [1, null] => $dateTimeNow ,
                    [2, $dateTimeTokenExpiration->getTimestamp()] => $dateTimeTokenExpiration,
                    [3, null] => $dateTimeTokenExpiration,
                    default => throw new \LogicException()
                };
            });

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        $this->assertTrue(property_exists($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}, 'expire'));
        $this->assertFalse($hasExpired);
    }

    #[Test]
    public function itShouldNotBeExpiredTimeToExpireIsHigherThanTimeNow(): void
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));
        $intervalLessOneSecond = new \DateInterval('PT1S');
        $intervalLessOneSecond->invert = true;
        $dateTimeTokenExpirationLessOne = (clone $dateTimeTokenExpiration)->add($intervalLessOneSecond);

        $matcher = $this->exactly(3);
        $this->object
            ->expects($matcher)
            ->method('getDateTime')
            ->willReturnCallback(function (float $timestamp) use ($matcher, $dateTimeTokenExpiration, $dateTimeNow, $dateTimeTokenExpirationLessOne) {
                $expectedNumCall = $matcher->numberOfInvocations();

                return match ([$expectedNumCall, $timestamp]) {
                    [1, null] => $dateTimeNow,
                    [2, $dateTimeTokenExpiration->getTimestamp()] => $dateTimeTokenExpiration,
                    [3, null] => $dateTimeTokenExpirationLessOne,
                    default => throw new \LogicException()
                };
            });

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        $this->assertTrue(property_exists($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}, 'expire'));
        $this->assertFalse($hasExpired);
    }

    #[Test]
    public function itShouldBeExpiredTimeToExpireIsLowerThanTimeNow(): void
    {
        $expirationInSeconds = 60 * 60 * 24;
        $dateTimeNow = new \DateTime();
        $dateTimeTokenExpiration = (clone $dateTimeNow)->add(new \DateInterval("PT{$expirationInSeconds}S"));
        $dataTimeTokenExpirationPlusOne = (clone $dateTimeTokenExpiration)->add(new \DateInterval('PT1S'));

        $matcher = $this->exactly(3);
        $this->object
            ->expects($matcher)
            ->method('getDateTime')
            ->willReturnCallback(function (float $timestamp) use ($matcher, $dateTimeNow, $dateTimeTokenExpiration, $dataTimeTokenExpirationPlusOne) {
                $expectedNumCall = $matcher->numberOfInvocations();

                return match ([$expectedNumCall, $timestamp]) {
                    [1, null] => $dateTimeNow,
                    [2, $dateTimeTokenExpiration->getTimestamp()] => $dateTimeTokenExpiration,
                    [3, null] => $dataTimeTokenExpirationPlusOne,
                    default => throw new \LogicException()
                };
            });

        $token = $this->object->encode(self::PAYLOAD, $expirationInSeconds);
        $tokenDecoded = $this->object->decode($token);
        $hasExpired = $this->object->hasExpired($tokenDecoded);

        $this->assertTrue(property_exists($tokenDecoded, JwtFirebaseHS256Adapter::KEY_TOKEN_DATA));
        $this->assertTrue(property_exists($tokenDecoded->{JwtFirebaseHS256Adapter::KEY_TOKEN_DATA}, 'expire'));
        $this->assertTrue($hasExpired);
    }
}
