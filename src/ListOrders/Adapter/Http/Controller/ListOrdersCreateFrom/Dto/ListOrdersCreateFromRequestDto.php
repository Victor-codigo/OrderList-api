<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersCreateFrom\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersCreateFromRequestDto implements RequestDtoInterface
{
    public readonly ?string $listOrdersIdCreateFrom;
    public readonly ?string $groupId;
    public readonly ?string $name;

    public function __construct(Request $request)
    {
        $this->listOrdersIdCreateFrom = $request->request->get('list_orders_id_create_from');
        $this->groupId = $request->request->get('group_id');
        $this->name = $request->request->get('name');
    }
}
