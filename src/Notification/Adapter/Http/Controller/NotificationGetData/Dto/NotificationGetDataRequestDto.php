<?php

declare(strict_types=1);

namespace Notification\Adapter\Http\Controller\NotificationGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class NotificationGetDataRequestDto implements RequestDtoInterface
{
    public readonly int|null $page;
    public readonly int|null $pageItems;
    public readonly string|null $lang;

    public function __construct(Request $request)
    {
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
        $this->lang = $request->query->get('lang', 'en');
    }
}
