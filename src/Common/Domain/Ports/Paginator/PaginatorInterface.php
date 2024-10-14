<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Paginator;

use Countable;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @template TKey
 *
 * @template-covariant TValue
 *
 * @phpstan-extends \IteratorAggregate<TKey, TValue>
 */
interface PaginatorInterface extends \IteratorAggregate, Countable
{
    /**
     * @return PaginatorInterface<int, mixed>
     */
    public function createPaginator(Query|QueryBuilder $query): self;

    public function getPageItems(): int;

    /**
     * @return PaginatorInterface<int, mixed>
     */
    public function setPagination(int $page = 1, int $pageItems = 100): self;

    /**
     * @return \Generator<\Traversable<mixed>>
     *
     * @throws InvalidArgumentException
     */
    public function getPagesRange(int $pageIni, int $pageEnd, int $pageItems): \Generator;

    /**
     * @return \Generator<\Traversable<mixed>>
     *
     * @throws InvalidArgumentException
     */
    public function getAllPages(int $pageItems): \Generator;

    public function getPageCurrent(): int;

    public function getPagesTotal(): int;

    public function hasNext(): bool;

    public function hasPrevious(): bool;

    public function getPageNextNumber(): ?int;

    public function getPagePreviousNumber(): ?int;

    public function getItemsTotal(): int;
}
