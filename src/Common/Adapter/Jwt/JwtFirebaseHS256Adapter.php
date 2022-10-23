<?php

declare(strict_types=1);

namespace Common\Adapter\Jwt;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use DateInterval;
use DateTime;
use DomainException as NativeDomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException as NativeInvalidArgumentException;
use UnexpectedValueException as NativeUnexpectedValueException;
use stdClass;

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

    /**
     * @throws InvalidArgumentException
     * @throws JwtException
     * @throws JwtTokenExpiredException
     */
    public function decode(string $token): stdClass
    {
        try {
            return JWT::decode($token, $this->secretKey);
        } catch (NativeInvalidArgumentException) {
            throw InvalidArgumentException::fromMessage('Provided key/key-array was empty');
        } catch (NativeDomainException) {
            throw JwtException::fromMessage('Provided JWT is malformed');
        } catch (NativeUnexpectedValueException) {
            throw JwtException::fromMessage('Provided JWT was invalid');
        } catch (SignatureInvalidException) {
            throw JwtException::fromMessage('Provided JWT was invalid because the signature verification failed');
        } catch (BeforeValidException) {
            throw JwtException::fromMessage('Provided JWT is trying to be used before it\'s eligible as defined by \'nbf\'');
        } catch (BeforeValidException) {
            throw JwtException::fromMessage('Provided JWT is trying to be used before it\'s been created as defined by \'iat\'');
        } catch (ExpiredException) {
            throw JwtTokenExpiredException::fromMessage('Provided JWT has since expired, as defined by the \'exp\' claim');
        }
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
