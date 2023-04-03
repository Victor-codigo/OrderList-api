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

    public function __construct(Request $request)
    {
        $this->userId = $request->request->get('users_id');
        $this->notificationType = $request->request->get('type');
    }
}
