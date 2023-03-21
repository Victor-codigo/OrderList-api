<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HttpClient;

interface HttpClientInterface
{
    /**
     * @throws UnsupportedOptionException
     */
    public function request(string $method, string $url, array $options = []): HttpClientResponseInterface;

    public function getNewInstance(array $options = []): static;
}
