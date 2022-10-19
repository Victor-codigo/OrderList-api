<?php

declare(strict_types=1);

namespace Common\Domain\Response;

class ResponseDto
{
    public RESPONSE_STATUS $status;
    public string $message;
    public array $data;
    public array $errors;

    public function getStatus(): RESPONSE_STATUS
    {
        return $this->status;
    }

    public function setStatus(RESPONSE_STATUS $status)
    {
        $this->status = $status;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function __construct(array $data = [], array $errors = [], string $message = '', RESPONSE_STATUS $status = RESPONSE_STATUS::OK)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
