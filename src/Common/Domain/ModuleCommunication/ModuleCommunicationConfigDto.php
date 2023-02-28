<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

class ModuleCommunicationConfigDto
{
    public function __construct(
        public readonly string $route,
        public readonly string $method,
        public readonly array $parameters,
        public readonly string $contentType,
        public readonly array $context,
        public readonly bool $authentication,
    ) {
    }
}
