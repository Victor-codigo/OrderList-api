<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetOrders\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersGetOrdersRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly string|null $listOrderId;
    public readonly int|null $page;
    public readonly int|null $page_items;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->listOrderId = $request->query->get('list_order_id');
        $this->page = $request->query->getInt('page');
        $this->page_items = $request->query->getInt('page_items');
    }
}
