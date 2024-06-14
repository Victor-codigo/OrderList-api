<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

interface ModuleCommunicationConfigDtoPaginatorInterface
{
    /** @param UploadedFileInterface[] $files */
    public function __construct(
        string $route,
        string $method,
        bool $authentication,
        array $attributes,
        array $query,
        array $files,
        string $contentType,
        array $content,
        array $cookies,
        array $headers,
    );

    public function cloneWithPage(int $page): self;

    public function getPage(): int;

    /**
     * Gets the path (separated by ".") where pages total is set in array response.
     */
    public function getResponsePagesTotalPath(): string;
}
