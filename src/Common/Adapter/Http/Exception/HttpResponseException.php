<?php

declare(strict_types=1);

namespace Common\Adapter\Http\Exception;

use Common\Domain\Response\ResponseDto;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HttpResponseException extends Exception implements HttpExceptionInterface
{
    private ResponseDto $responseData;
    private int $statusCode;
    private array $headers = [];

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->responseData = new ResponseDto();
        $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        $this->headers = [];
    }

    public function setResponseData(ResponseDto $data): static
    {
        $this->responseData = $data;

        return $this;
    }

    public function getResponseData(): ResponseDto
    {
        return $this->responseData;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setHeaders(array $headers): static
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
