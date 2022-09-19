<?php

declare(strict_types=1);

namespace User\Domain\Port\Repository;

use User\Domain\Model\Profile;

interface ProfileRepositoryInterface
{
    public function save(Profile $user): void;

    public function remove(profile $user): void;
}
