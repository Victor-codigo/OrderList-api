<?php

declare(strict_types=1);

namespace Common\Adapter\HttpClient;

use Override;
use Common\Domain\HttpClient\Exception\UnsupportedOptionException;
use Common\Domain\Ports\HttpClient\HttpClientInterface;
use Common\Domain\Ports\HttpClient\HttpClientResponseInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyHttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientSymfonyAdapter implements HttpClientInterface
{
    private SymfonyHttpClientInterface $httpClient;

    public function __construct(SymfonyHttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @throws UnsupportedOptionException
     */
    #[Override]
    public function request(string $method, string $url, array $options = []): HttpClientResponseInterface
    {
        try {
            return $this->createResponse(
                $this->httpClient->request($method, $url, $options)
            );
        } catch (TransportExceptionInterface $e) {
            throw UnsupportedOptionException::fromMessage($e->getMessage());
        }
    }

    #[Override]
    public function getNewInstance(array $options = []): static
    {
        return new static($this->httpClient->withOptions($options));
    }

    private function createResponse(ResponseInterface $response): HttpClientResponseInterface
    {
        return new HttpClientResponse($response);
    }
}
