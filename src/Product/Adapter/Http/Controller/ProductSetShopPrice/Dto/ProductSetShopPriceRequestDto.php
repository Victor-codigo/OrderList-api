<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductSetShopPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductSetShopPriceRequestDto implements RequestDtoInterface
{
    public readonly string|null $productId;
    public readonly string|null $shopId;
    public readonly string|null $groupId;
    public readonly float|null $price;

    public function __construct(Request $request)
    {
        $this->productId = $request->request->get('product_id');
        $this->shopId = $request->request->get('shop_id');
        $this->groupId = $request->request->get('group_id');
        $this->price = $request->request->get('price');
    }
}
