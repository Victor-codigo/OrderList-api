<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ProductRemoveRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    public readonly ?array $productsId;
    public readonly ?array $shopsId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->productsId = $request->request->all('products_id');
        $this->shopsId = $request->request->all('shops_id');
    }
}
