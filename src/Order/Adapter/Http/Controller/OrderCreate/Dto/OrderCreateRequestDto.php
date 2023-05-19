<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderCreate\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderCreateRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly array|null $ordersData;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->ordersData = $request->request->all('orders_data');
    }
}
