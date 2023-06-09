<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemoveOrder\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersRemoveOrderRequestDto implements RequestDtoInterface
{
    public readonly array|null $ordersId;
    public readonly string|null $listOrdersId;
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->ordersId = $request->request->all('orders_id');
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->groupId = $request->request->get('group_id');
    }
}
