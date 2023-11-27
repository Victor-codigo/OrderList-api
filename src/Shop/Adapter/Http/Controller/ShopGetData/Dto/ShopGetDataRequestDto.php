<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ShopGetDataRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const SHOPS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_SHOPS_MAX;
    private const PRODUCTS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_PRODUCTS_MAX;

    public readonly string|null $groupId;
    public readonly array|null $shopsId;
    public readonly array|null $productsId;
    public readonly string|null $shopNameStartsWith;
    public readonly string|null $shopName;
    public readonly bool|null $orderArc;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->shopsId = $this->validateCsvOverflow($request->query->get('shops_id'), self::SHOPS_NUM_MAX);
        $this->productsId = $this->validateCsvOverflow($request->query->get('products_id'), self::PRODUCTS_NUM_MAX);
        $this->shopNameStartsWith = $request->query->get('shop_name_starts_with');
        $this->shopName = $request->query->get('shop_name');
        $this->orderArc = $request->query->getBoolean('order_asc', true);
    }
}
