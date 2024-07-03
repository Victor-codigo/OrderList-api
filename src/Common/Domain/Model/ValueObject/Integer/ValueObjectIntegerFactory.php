<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

class ValueObjectIntegerFactory
{
    public static function createPaginatorPage(?int $page): PaginatorPage
    {
        return new PaginatorPage($page);
    }

    public static function createPaginatorPageItems(?int $pageItems): PaginatorPageItems
    {
        return new PaginatorPageItems($pageItems);
    }
}
