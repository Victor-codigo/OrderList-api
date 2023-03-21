<?php

declare(strict_types=1);

namespace Common\Domain\Response;

class ResponseDto
{
    public RESPONSE_STATUS $status;
    public string $message;
    public array $data;
    public array $headers;
    public array $errors;
    public bool $hasContent;

    public function getStatus(): RESPONSE_STATUS
    {
        return $this->status;
    }

    public function setStatus(RESPONSE_STATUS $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function hasContent(): bool
    {
        return $this->hasContent;
    }

    public function __construct(array $data = [], array $errors = [], string $message = '', RESPONSE_STATUS $status = RESPONSE_STATUS::OK, bool $hasContent = true, array $headers = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->headers = $headers;
        $this->errors = $errors;
        $this->hasContent = $hasContent;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'data' => $this->data,
            'headers' => $this->headers,
            'errors' => $this->errors,
            'hasContent' => $this->hasContent,
        ];
    }
}
