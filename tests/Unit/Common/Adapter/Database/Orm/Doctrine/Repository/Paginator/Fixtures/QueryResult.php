<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Fixtures;

class QueryResult
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $age
    ) {
    }
}
