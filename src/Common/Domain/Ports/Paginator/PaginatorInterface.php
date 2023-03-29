<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

interface PaginatorInterface extends \IteratorAggregate, \Countable
{
    public function createPaginator(Query|QueryBuilder $query): self;

    public function getPageItems(): int;

    public function setPagination(int $page = 1, int $pageItems = 100): self;

    public function getPageCurrent(): int;

    public function getPagesTotal(): int;

    public function hasNext(): bool;

    public function hasPrevious(): bool;

    public function getPageNextNumber(): int|null;

    public function getPagePreviousNumber(): int|null;

    public function getItemsTotal(): int;
}
