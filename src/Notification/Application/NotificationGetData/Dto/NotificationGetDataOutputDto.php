<?php

declare(strict_types=1);

namespace Notification\Application\NotificationGetData\Dto;

class NotificationGetDataOutputDto
{
    /**
     * @param array<int, array{
     *  id: string|null,
     *  type: string|null,
     *  user_id: string|null,
     *  message: string|null,
     *  data: array<string, string|int|float>,
     *  viewed: bool,
     *  created_on: string
     * }> $notificationsData
     */
    public function __construct(
        public readonly array $notificationsData,
    ) {
    }
}
