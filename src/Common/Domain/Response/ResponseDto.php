<?php

declare(strict_types=1);

namespace Common\Domain\Response;

class ResponseDto
{
    public RESPONSE_STATUS $status;
    public string $message;
    /**
     * @var mixed[]
     */
    public array $data;
    /**
     * @var string[]
     */
    public array $headers;
    /**
     * @var mixed[]
     */
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

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param mixed[] $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param mixed[] $errors
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    public function hasContent(): bool
    {
        return $this->hasContent;
    }

    /**
     * @param mixed[]  $data
     * @param mixed[]  $errors
     * @param string[] $headers
     */
    public function __construct(array $data = [], array $errors = [], string $message = '', RESPONSE_STATUS $status = RESPONSE_STATUS::OK, bool $hasContent = true, array $headers = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->headers = $headers;
        $this->errors = $errors;
        $this->hasContent = $hasContent;
    }

    /**
     * @return array{
     *  status: int|string,
     *  message: string,
     *  data: mixed[],
     *  headers: string[],
     *  errors: mixed[],
     *  hasContent: bool
     * }
     */
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

    public function to(callable $callbackTransformTo, bool $multidimensional = true): mixed
    {
        if (!$multidimensional) {
            return $callbackTransformTo($this->data);
        }

        return array_map(
            fn (mixed $data) => $callbackTransformTo($data),
            $this->data
        );
    }

    public function validate(bool $content = true): bool
    {
        if (!empty($this->errors)) {
            return false;
        }

        if ($content && !$this->hasContent) {
            return false;
        }

        return true;
    }
}
