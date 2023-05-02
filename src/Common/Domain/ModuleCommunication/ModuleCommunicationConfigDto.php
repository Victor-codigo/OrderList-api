<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

class ModuleCommunicationConfigDto
{
    /** @param UploadedFileInterface[] $files */
    public function __construct(
        public readonly string $route,
        public readonly string $method,
        public readonly bool $authentication,
        public readonly array $attributes,
        public readonly array $query,
        public readonly array $files,
        public readonly string $contentType,
        public readonly array $content,
        public readonly array $cookies,
        public readonly array $headers,
    ) {
    }
}
