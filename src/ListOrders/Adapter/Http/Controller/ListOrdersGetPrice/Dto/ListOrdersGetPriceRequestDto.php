<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersGetPriceRequestDto implements RequestDtoInterface
{
    public readonly ?string $listOrdersId;
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->listOrdersId = $request->query->get('list_orders_id');
        $this->groupId = $request->query->get('group_id');
    }
}
