<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository\Paginator;

use Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Exception\PaginatorPageException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DoctrinePaginatorAdapter implements PaginatorInterface, \IteratorAggregate
{
    private const MAX_RESULT_DEFAULT = 100;

    private Paginator $paginator;

    public function __construct(Query $query)
    {
        $this->paginator = new Paginator($query);
        $this->paginator
            ->getQuery()
            ->setMaxResults(self::MAX_RESULT_DEFAULT);
        $this->setPage(1);
    }

    public function setPageItems(int $pageItems = 100): self
    {
        if ($pageItems <= 0) {
            throw InvalidArgumentException::fromMessage('Page items must be bigger than 0');
        }

        $this->paginator
            ->getQuery()
            ->setMaxResults($pageItems);

        return $this;
    }

    public function getPageItems(): int
    {
        return $this->paginator
            ->getQuery()
            ->getMaxResults();
    }

    public function setPage(int $page): self
    {
        if ($page <= 0) {
            throw PaginatorPageException::formMessage('Wrong page. Page must be bigger than 1');
        }

        if ($page > $this->getPagesTotal()) {
            throw PaginatorPageException::formMessage("Wrong page. Page must be lower than pages total: {$this->getPagesTotal()}");
        }

        $this->paginator
            ->getQuery()
            ->setFirstResult($this->getPageOffset($page));

        return $this;
    }

    public function getPageCurrent(): int
    {
        $pageItems = $this->getPageItems();
        $firstResult = $this->paginator
            ->getQuery()
            ->getFirstResult();

        return $firstResult < $pageItems
                ? 1
                : (int) floor($firstResult / $pageItems) + 1;
    }

    public function getPagesTotal(): int
    {
        $itemsTotal = $this->getItemsTotal();

        return 0 !== $itemsTotal
            ? (int) ceil($this->getItemsTotal() / $this->getPageItems())
            : 1;
    }

    public function hasNext(): bool
    {
        return $this->getPageCurrent() < $this->getPagesTotal();
    }

    public function hasPrevious(): bool
    {
        return $this->getPageCurrent() > 1;
    }

    public function getPageNextNumber(): int|null
    {
        if (!$this->hasNext()) {
            return null;
        }

        return $this->getPageCurrent() + 1;
    }

    public function getPagePreviousNumber(): int|null
    {
        if (!$this->hasPrevious()) {
            return null;
        }

        return $this->getPageCurrent() - 1;
    }

    public function getItemsTotal(): int
    {
        return count($this->paginator);
    }

    public function getIterator(): \Traversable
    {
        return $this->paginator->getIterator();
    }

    private function getPageOffset(int $page): int
    {
        return ($page - 1) * $this->getPageItems();
    }
}
