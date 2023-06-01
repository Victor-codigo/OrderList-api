<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersModifyRequestDto implements RequestDtoInterface
{
    public readonly string|null $listOrdersId;
    public readonly string|null $groupId;
    public readonly string|null $name;
    public readonly string|null $description;
    public readonly string|null $dateToBuy;

    public function __construct(Request $request)
    {
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->groupId = $request->request->get('group_id');
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
        $this->dateToBuy = $request->request->get('date_to_buy');
    }
}
