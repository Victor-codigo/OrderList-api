<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HttpClient;

interface HttpClientResponseInterface
{
    public function close(): void;

    /**
     * @throws NetworkException
     */
    public function getStatusCode(): int;

    /**
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function getContent(bool $throwException = true): string;

    /**
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function getHeaders(bool $throwException = true): array;

    public function getInfo(bool|null $throwException = true): array;

    /**
     * @throws DecodingException
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function toArray(bool $throwException = true): array;
}
