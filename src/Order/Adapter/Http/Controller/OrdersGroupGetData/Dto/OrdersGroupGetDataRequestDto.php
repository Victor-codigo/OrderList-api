<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrdersGroupGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrdersGroupGetDataRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    public readonly ?int $page;
    public readonly ?int $pageItems;
    public readonly bool $orderAsc;

    public function __construct(Request $request)
    {
        $this->groupId = $request->attributes->get('group_id');
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
        $this->orderAsc = $request->query->getBoolean('order_asc', true);
    }
}
