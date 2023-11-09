<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderModifyRequestDto implements RequestDtoInterface
{
    public readonly string|null $orderId;
    public readonly string|null $groupId;
    public readonly string|null $productId;
    public readonly string|null $shopId;
    public readonly string|null $description;
    public readonly float|null $amount;
    public readonly string|null $unit;

    public function __construct(Request $request)
    {
        $this->orderId = $request->request->get('order_id');
        $this->groupId = $request->request->get('group_id');
        $this->productId = $request->request->get('product_id');
        $this->shopId = $request->request->get('shop_id');
        $this->description = $request->request->get('description');
        $this->amount = (float) $request->request->get('amount');
        $this->unit = $request->request->get('unit');
    }
}
