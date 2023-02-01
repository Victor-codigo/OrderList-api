<?php

declare(strict_types=1);

namespace Common\Adapter\HttpClient;

use Common\Domain\HttpClient\Exception\UnsuportedOptionException;
use Common\Domain\Ports\HttpCllent\HttpClientInterface;
use Common\Domain\Ports\HttpCllent\HttpClientResponseInteface;
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
     * @throws UnsuportedOptionException
     */
    public function request(string $method, string $url, array $options = []): HttpClientResponseInteface
    {
        try {
            return $this->createResponse(
                $this->httpClient->request($method, $url, $options)
            );
        } catch (TransportExceptionInterface $e) {
            throw UnsuportedOptionException::fromMessage($e->getMessage());
        }
    }

    public function getNewInstance(array $options = []): static
    {
        return new static($this->httpClient->withOptions($options));
    }

    private function createResponse(ResponseInterface $response): HttpClientResponse
    {
        return new HttpClientResponse($response);
    }
}
