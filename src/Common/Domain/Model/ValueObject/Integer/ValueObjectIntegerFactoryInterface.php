<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

interface ValueObjectIntegerFactoryInterface
{
    public static function createPaginatorPage(int|null $page): PaginatorPage;

    public static function createPaginatorPageItems(int|null $pageItems): PaginatorPageItems;
}
