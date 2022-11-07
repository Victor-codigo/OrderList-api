<?php

declare(strict_types=1);

namespace Common\Adapter\Jwt;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use JWT\Authentication\JWT;

class JwtLexikAdapter implements JwtHS256Interface
{
    public const ALGORITM = 'HS256';
    public const KEY_ISSUED_AT = 'iat';
    public const KEY_EXPIRE = 'exp';

    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function encode(array $data, float $expireTimeInSeconds = 3600): string
    {
        $data[self::KEY_ISSUED_AT] = (new \DateTimeImmutable())->getTimestamp();
        $data[self::KEY_EXPIRE] = $data[self::KEY_ISSUED_AT] + $expireTimeInSeconds;
        $token = JWT::encode($data, $this->secretKey, self::ALGORITM);

        return $token;
    }

    /**
     * @throws JwtException
     */
    public function decode(string $token): \stdClass
    {
        try {
            return JWT::decode($token, $this->secretKey, true);
        } catch (\Exception $e) {
            throw JwtException::fromMessage('Provided JWT was invalid');
        }
    }

    public function hasExpired(object $tokenDecoded): bool
    {
        if (!isset($tokenDecoded->{self::KEY_ISSUED_AT}) || !isset($tokenDecoded->{self::KEY_EXPIRE})) {
            return true;
        }

        return $this->getDateTime($tokenDecoded->{self::KEY_EXPIRE}) < $this->getDateTime();
    }

    protected function getDateTime(int|null $timestamp = null): \DateTime
    {
        if (null === $timestamp) {
            return new \DateTime();
        }

        return (new \DateTime())->setTimestamp($timestamp);
    }
}
