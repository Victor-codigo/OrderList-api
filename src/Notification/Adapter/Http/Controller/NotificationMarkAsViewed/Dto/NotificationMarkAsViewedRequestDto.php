<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationMarkAsViewed\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class NotificationMarkAsViewedRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int NOTIFICATIONS_NUM_MAX = AppConfig::ENDPOINT_NOTIFICATION_MARK_AS_VIEWED_MAX;

    /**
     * @var string[]|null
     */
    public readonly ?array $notificationsId;

    public function __construct(Request $request)
    {
        $this->notificationsId = $this->validateArrayOverflow($request->request->all('notifications_id'), self::NOTIFICATIONS_NUM_MAX);
    }
}
