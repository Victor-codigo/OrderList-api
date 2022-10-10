<?php

declare(strict_types=1);

namespace Common\Domain\Response;

class ResponseDto
{
    public readonly RESPONSE_STATUS $status;
    public readonly string $message;
    public readonly array $data;
    public readonly array $errors;

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
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
        ];
    }
}
