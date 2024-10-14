<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HttpClient;

interface HttpClientInterface
{
    /**
     * @param mixed[] $options
     *
     * @throws UnsupportedOptionException
     */
    public function request(string $method, string $url, array $options = []): HttpClientResponseInterface;

    /**
     * @param mixed[] $options
     */
    public function getNewInstance(array $options = []): static;
}
