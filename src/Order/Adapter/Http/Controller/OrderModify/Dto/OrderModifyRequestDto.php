<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class OrderModifyRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    public readonly ?string $orderId;
    public readonly ?string $listOrdersId;
    public readonly ?string $productId;
    public readonly ?string $shopId;
    public readonly ?string $description;
    public readonly ?float $amount;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->orderId = $request->request->get('order_id');
        $this->productId = $request->request->get('product_id');
        $this->shopId = $request->request->get('shop_id');
        $this->description = $request->request->get('description');
        $this->amount = (float) $request->request->get('amount');
    }
}
