<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationRemoveAllUserNotifications\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class NotificationRemoveAllUserNotificationsRequestDto implements RequestDtoInterface
{
    public readonly ?string $systemKey;

    public function __construct(Request $request)
    {
        $this->systemKey = $request->request->get('system_key');
    }
}
