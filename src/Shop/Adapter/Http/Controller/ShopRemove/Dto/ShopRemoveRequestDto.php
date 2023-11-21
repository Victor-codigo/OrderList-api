<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ShopRemoveRequestDto implements RequestDtoInterface
{
    public readonly string|null $shopId;
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->shopId = $request->request->get('shop_id');
        $this->groupId = $request->request->get('group_id');
    }
}
