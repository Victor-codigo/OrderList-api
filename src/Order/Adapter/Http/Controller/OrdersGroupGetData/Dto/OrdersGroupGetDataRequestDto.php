<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrdersGroupGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrdersGroupGetDataRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly int|null $page;
    public readonly int|null $pageItems;

    public function __construct(Request $request)
    {
        $this->groupId = $request->attributes->get('group_id');
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
