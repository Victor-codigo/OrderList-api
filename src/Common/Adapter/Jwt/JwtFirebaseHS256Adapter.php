<?php

declare(strict_types=1);

namespace Common\Adapter\Jwt;

use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtFirebaseHS256Adapter implements JwtHS256Interface
{
    public const ALGORITM = 'HS256';
    public const KEY_TOKEN_DATA = '__jwt_data';

    private readonly Key $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $this->getKey($secretKey);
    }

    public function encode(array $data, float|null $expireTimeInSeconds = null): string
    {
        if (null !== $expireTimeInSeconds) {
            $data[self::KEY_TOKEN_DATA]['expire'] = $this->getDateTime()
                ->add(new DateInterval("PT{$expireTimeInSeconds}S"))
                ->getTimestamp();
        }

        return JWT::encode($data, $this->secretKey->getKeyMaterial(), self::ALGORITM);
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, $this->secretKey);
    }

    public function hasExpired(object $tokenDecoded): bool
    {
        if (!isset($tokenDecoded->{self::KEY_TOKEN_DATA}) || !isset($tokenDecoded->{self::KEY_TOKEN_DATA}->expire)) {
            return false;
        }

        return $this->getDateTime($tokenDecoded->{self::KEY_TOKEN_DATA}->expire) < $this->getDateTime();
    }

    protected function getDateTime(int|null $timestamp = null): DateTime
    {
        if (null === $timestamp) {
            return new DateTime();
        }

        return (new DateTime())->setTimestamp($timestamp);
    }

    protected function getKey(string $secretKey)
    {
        return new Key($secretKey, self::ALGORITM);
    }
}
