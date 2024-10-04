<?php

declare(strict_types=1);

namespace Share\Domain\Port\Repository;

use Common\Domain\Ports\Repository\RepositoryInterface;
use Share\Domain\Model\Share;

interface ShareRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Share $share): void;

    /**
     * @param Share[] $share
     *
     * @throws DBConnectionException
     */
    public function remove(array $share): void;
}
