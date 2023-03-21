<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

class ModuleCommunicationConfigDto
{
    public function __construct(
        public readonly string $route,
        public readonly string $method,
        public readonly array $attributes,
        public readonly array $query,
        /** @param UploadedFileInterface $files */
        public readonly array $files,
        public readonly string $contentType,
        public readonly array $content,
        public readonly array $cookies,
        public readonly bool $authentication,
    ) {
    }
}
