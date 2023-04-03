<?php

declare(strict_types=1);

namespace Notification\Domain\Ports\Notification;

use Common\Domain\Ports\Repository\RepositoryInterface;
use Notification\Domain\Model\Notification;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Notification[] $notifications
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $notifications): void;
}
