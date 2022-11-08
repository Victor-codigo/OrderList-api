<?php

declare(strict_types=1);

namespace Common\Domain\Ports\JwtToken;

interface JwtHS256Interface
{
    public function encode(array $data, float $expire = 3600): string;

    /**
     * @throws JwtException
     */
    public function decode(string $token): object;

    public function hasExpired(object $tokenDecoded): bool;
}
