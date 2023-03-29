<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

class ValueObjectIntegerFactory
{
    public static function createPaginatorPage(int|null $page): PaginatorPage
    {
        return new PaginatorPage($page);
    }

    public static function createPaginatorPageItems(int|null $pageItems): PaginatorPageItems
    {
        return new PaginatorPageItems($pageItems);
    }
}
