<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class NotificationRemoveRequestDto implements RequestDtoInterface
{
    private const NOTIFICATIONS_NUM_MAX = AppConfig::ENDPOINT_NOTIFICATION_REMOVE_MAX;

    public readonly array|null $notificationsId;

    public function __construct(Request $request)
    {
        $this->notificationsId = $this->removeNotificationsOverflow($request->attributes->get('notifications_id'));
    }

    private function removeNotificationsOverflow(string|null $notificationsId): array|null
    {
        if (null === $notificationsId) {
            return null;
        }

        $notificationsIdValid = explode(',', $notificationsId, self::NOTIFICATIONS_NUM_MAX + 1);

        if (count($notificationsIdValid) > self::NOTIFICATIONS_NUM_MAX) {
            $notificationsIdValid = array_slice($notificationsIdValid, 0, self::NOTIFICATIONS_NUM_MAX);
        }

        return $notificationsIdValid;
    }
}
