<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class NotificationCreateRequestDto implements RequestDtoInterface
{
    /**
     * @var string[]|null
     */
    public readonly array|null $userId;
    public readonly string|null $notificationType;
    public readonly array|null $notificationData;
    public readonly string|null $systemKey;

    public function __construct(Request $request)
    {
        $this->userId = $request->request->get('users_id');
        $this->notificationType = $request->request->get('type');
        $this->notificationData = $request->request->get('notification_data');
        $this->systemKey = $request->request->get('system_key');
    }
}
