<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderCreateRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    public readonly ?string $listOrdersId;
    public readonly ?array $ordersData;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->ordersData = $request->request->all('orders_data');
    }
}
