<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Ports\FileUpload\UploadedFileInterface;

interface ModuleCommunicationConfigDtoPaginatorInterface
{
    /**
     * @param array<int|string, mixed> $attributes
     * @param array<int|string, mixed> $query
     * @param UploadedFileInterface[]  $files
     * @param array<int|string, mixed> $content
     * @param array<int|string, mixed> $cookies
     * @param array<int|string, mixed> $headers
     */
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

    public function getRoute(): string;

    public function getMethod(): string;

    public function getAuthentication(): bool;

    /**
     * @return array<int|string, mixed>
     */
    public function getAttributes(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getQuery(): array;

    /**
     * @return UploadedFileInterface[]
     */
    public function getFiles(): array;

    public function getContentType(): string;

    /**
     * @return array<int|string, mixed>
     */
    public function getContent(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getCookies(): array;

    /**
     * @return array<int|string, mixed>
     */
    public function getHeaders(): array;
}
