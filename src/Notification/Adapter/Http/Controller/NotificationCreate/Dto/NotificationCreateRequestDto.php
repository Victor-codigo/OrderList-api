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
    public readonly ?array $userId;
    public readonly ?string $notificationType;
    public readonly ?array $notificationData;
    public readonly ?string $systemKey;

    public function __construct(Request $request)
    {
        $this->userId = $request->request->all('users_id');
        $this->notificationType = $request->request->get('type');
        $this->notificationData = $request->request->all('notification_data');
        $this->systemKey = $request->request->get('system_key');
    }
}
