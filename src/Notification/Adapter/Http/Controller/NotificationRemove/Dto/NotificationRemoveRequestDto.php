<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class NotificationRemoveRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int NOTIFICATIONS_NUM_MAX = AppConfig::ENDPOINT_NOTIFICATION_REMOVE_MAX;

    public readonly ?array $notificationsId;

    public function __construct(Request $request)
    {
        $this->notificationsId = $this->validateCsvOverflow($request->query->get('notifications_id'), self::NOTIFICATIONS_NUM_MAX);
    }
}
