<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderBought\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderBoughtRequestDto implements RequestDtoInterface
{
    public readonly ?string $orderId;
    public readonly ?string $groupId;
    public readonly bool $bought;

    public function __construct(Request $request)
    {
        $this->orderId = $request->request->get('order_id');
        $this->groupId = $request->request->get('group_id');
        $this->bought = $request->request->getBoolean('bought');
    }
}
