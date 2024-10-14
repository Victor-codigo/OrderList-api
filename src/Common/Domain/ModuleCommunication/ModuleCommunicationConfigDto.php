<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Ports\FileUpload\UploadedFileInterface;

class ModuleCommunicationConfigDto implements ModuleCommunicationConfigDtoPaginatorInterface
{
    /**
     * @param array<int|string, mixed> $attributes
     * @param array<int|string, mixed> $query
     * @param UploadedFileInterface[]  $files
     * @param array<int|string, mixed> $content
     * @param array<int|string, mixed> $cookies
     * @param array<int|string, mixed> $headers
     * */
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

    /**
     * Gets the path (separated by ".") where pages total is set in array response.
     */
    #[\Override]
    public function getResponsePagesTotalPath(): string
    {
        return 'pages_total';
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

    /**
     * @return array<int|string, mixed>
     */
    #[\Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<int|string, mixed>
     */
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

    /**
     * @return array<int|string, mixed>
     */
    #[\Override]
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @return array<int|string, mixed>
     */
    #[\Override]
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * @return array<int|string, mixed>
     */
    #[\Override]
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
