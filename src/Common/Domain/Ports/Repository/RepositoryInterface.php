<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Repository;

interface RepositoryInterface
{
    public function generateId(): string;

    public function isValidUuid(string $id): bool;
}
