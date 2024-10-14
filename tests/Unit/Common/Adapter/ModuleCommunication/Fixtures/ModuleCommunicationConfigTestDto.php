<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication\Fixtures;

use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDtoPaginatorInterface;

class ModuleCommunicationConfigTestDto implements ModuleCommunicationConfigDtoPaginatorInterface
{
    private string $pagesTotalResponsePath;

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

    #[\Override]
    public function cloneWithPage(int $page): self
    {
        $query = $this->query;
        $query['page'] = $page;

        return new self(
            $this->route,
            $this->method,
            $this->authentication,
            $this->attributes,
            $query,
            $this->files,
            $this->contentType,
            $this->content,
            $this->cookies,
            $this->headers
        );
    }

    #[\Override]
    public function getPage(): int
    {
        return $this->query['page'];
    }

    #[\Override]
    public function getRoute(): string
    {
        return $this->route;
    }

    #[\Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    #[\Override]
    public function getAuthentication(): bool
    {
        return $this->authentication;
    }

    #[\Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    #[\Override]
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @return UploadedFileInterface[]
     */
    #[\Override]
    public function getFiles(): array
    {
        return $this->files;
    }

    #[\Override]
    public function getContentType(): string
    {
        return $this->contentType;
    }

    #[\Override]
    public function getContent(): array
    {
        return $this->content;
    }

    #[\Override]
    public function getCookies(): array
    {
        return $this->cookies;
    }

    #[\Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Gets the path (separated by ".") where pages total is set in array response.
     */
    #[\Override]
    public function getResponsePagesTotalPath(): string
    {
        if (null === $this->pagesTotalResponsePath) {
            return 'pages_total';
        }

        return $this->pagesTotalResponsePath;
    }

    public static function json(bool $authentication, array $content = [], array $query = [], array $files = [], array $cookies = [], array $headers = []): self
    {
        $attributes = [
            'api_version' => 1,
        ];

        return new self(
            'user_get',
            'GET',
            $authentication,
            $attributes,
            $query,
            $files,
            'application/json',
            $content,
            $cookies,
            $headers
        );
    }

    public function setResponsePagesTotalPath(string $pagesTotalPath): void
    {
        $this->pagesTotalResponsePath = $pagesTotalPath;
    }
}
