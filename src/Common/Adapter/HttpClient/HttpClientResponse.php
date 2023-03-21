<?php

declare(strict_types=1);

namespace Common\Adapter\HttpClient;

use Common\Domain\HttpClient\Exception\DecodingException;
use Common\Domain\HttpClient\Exception\Error300Exception;
use Common\Domain\HttpClient\Exception\Error400Exception;
use Common\Domain\HttpClient\Exception\Error500Exception;
use Common\Domain\HttpClient\Exception\NetworkException;
use Common\Domain\Ports\HttpClient\HttpClientResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class HttpClientResponse implements HttpClientResponseInterface
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function close(): void
    {
        $this->response->cancel();
    }

    /**
     * @throws NetworkException
     */
    public function getStatusCode(): int
    {
        try {
            return $this->response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw NetworkException::fromMessage($e->getMessage());
        }
    }

    /**
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function getContent(bool $throwException = true): string
    {
        try {
            return $this->response->getContent($throwException);
        } catch (TransportExceptionInterface $e) {
            throw NetworkException::fromMessage($e->getMessage());
        } catch (RedirectionExceptionInterface $e) {
            throw Error300Exception::fromMessage($e->getMessage(), $e);
        } catch (ClientExceptionInterface $e) {
            throw Error400Exception::fromMessage($e->getMessage(), $e);
        } catch (ServerExceptionInterface $e) {
            throw Error500Exception::fromMessage($e->getMessage(), $e);
        }
    }

    /**
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function getHeaders(bool $throwException = true): array
    {
        try {
            return $this->response->getHeaders($throwException);
        } catch (TransportExceptionInterface $e) {
            throw NetworkException::fromMessage($e->getMessage());
        } catch (RedirectionExceptionInterface $e) {
            throw Error300Exception::fromMessage($e->getMessage(), $e);
        } catch (ClientExceptionInterface $e) {
            throw Error400Exception::fromMessage($e->getMessage(), $e);
        } catch (ServerExceptionInterface $e) {
            throw Error500Exception::fromMessage($e->getMessage(), $e);
        }
    }

    public function getInfo(bool|null $throwException = true): array
    {
        return $this->response->getInfo($throwException);
    }

    /**
     * @throws DecodingException
     * @throws NetworkException
     * @throws Error300Exception
     * @throws Error400Exception
     * @throws Error500Exception
     */
    public function toArray(bool $throwException = true): array
    {
        try {
            return $this->response->toArray($throwException);
        } catch (DecodingExceptionInterface $e) {
            throw DecodingException::fromMessage($e->getMessage());
        } catch (TransportExceptionInterface $e) {
            throw NetworkException::fromMessage($e->getMessage());
        } catch (RedirectionExceptionInterface $e) {
            throw Error300Exception::fromMessage($e->getMessage(), $e);
        } catch (ClientExceptionInterface $e) {
            throw Error400Exception::fromMessage($e->getMessage(), $e);
        } catch (ServerExceptionInterface $e) {
            throw Error500Exception::fromMessage($e->getMessage(), $e);
        }
    }
}
