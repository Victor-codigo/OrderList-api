<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class NotificationGetDataRequestDto implements RequestDtoInterface
{
    public readonly int $page;
    public readonly int $pageItems;

    public function __construct(Request $request)
    {
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
