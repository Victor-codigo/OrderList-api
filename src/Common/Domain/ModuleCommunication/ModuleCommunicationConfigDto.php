<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

class ModuleCommunicationConfigDto implements ModuleCommunicationConfigDtoPaginatorInterface
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
}
