<?php

declare(strict_types=1);

namespace Common\Domain\Ports\HttpCllent;

interface HttpClientInterface
{
    /**
     * @throws UnsuportedOptionException
     */
    public function request(string $method, string $url, array $options = []): HttpClientResponseInteface;

    public function getNewInstance(array $options = []): static;
}
