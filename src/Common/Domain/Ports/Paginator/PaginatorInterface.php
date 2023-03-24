<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Paginator;

interface PaginatorInterface
{
    public function setPageItems(int $pageItems = 100): self;

    public function getPageItems(): int;

    public function setPage(int $page): self;

    public function getPageCurrent(): int;

    public function getPagesTotal(): int;

    public function hasNext(): bool;

    public function hasPrevious(): bool;

    public function getPageNextNumber(): int|null;

    public function getPagePreviousNumber(): int|null;

    public function getItemsTotal(): int;

    public function getIterator(): \Traversable;
}
