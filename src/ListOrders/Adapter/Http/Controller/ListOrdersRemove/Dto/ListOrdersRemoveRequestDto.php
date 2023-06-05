<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersRemoveRequestDto implements RequestDtoInterface
{
    public readonly string|null $listOrdersId;
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->groupId = $request->request->get('group_id');
    }
}
