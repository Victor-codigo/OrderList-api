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

    public function getPage(): int
    {
        return $this->query['page'];
    }

    /**
     * Gets the path (separated by ".") where pages total is set in array response.
     */
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
