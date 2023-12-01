<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ProductGetDataRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const PRODUCTS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_GET_PRODUCTS_MAX;
    private const SHOPS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_GET_SHOPS_MAX;

    public readonly string|null $groupId;
    public readonly array|null $productsId;
    public readonly array|null $shopsId;
    public readonly string|null $productName;

    public readonly string|null $productNameFilterType;
    public readonly string|null $productNameFilterValue;
    public readonly string|null $shopNameFilterType;
    public readonly string|null $shopNameFilterValue;

    public readonly bool $orderAsc;

    public readonly int $page;
    public readonly int $pageItems;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->productsId = $this->validateCsvOverflow($request->query->get('products_id'), self::PRODUCTS_NUM_MAX);
        $this->shopsId = $this->validateCsvOverflow($request->query->get('shops_id'), self::SHOPS_NUM_MAX);
        $this->productName = $request->query->get('product_name');

        $this->productNameFilterType = $request->query->get('product_name_filter_type');
        $this->productNameFilterValue = $request->query->get('product_name_filter_value');
        $this->shopNameFilterType = $request->query->get('shop_name_filter_type');
        $this->shopNameFilterValue = $request->query->get('shop_name_filter_value');

        $this->orderAsc = $request->query->getBoolean('order_asc', true);

        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
