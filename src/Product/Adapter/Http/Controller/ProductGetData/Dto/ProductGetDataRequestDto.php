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

    private const int PRODUCTS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_GET_PRODUCTS_MAX;
    private const int SHOPS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_GET_SHOPS_MAX;

    public readonly ?string $groupId;
    public readonly ?array $productsId;
    public readonly ?array $shopsId;
    public readonly ?string $productName;

    public readonly ?string $productNameFilterType;
    public readonly ?string $productNameFilterValue;
    public readonly ?string $shopNameFilterType;
    public readonly ?string $shopNameFilterValue;

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
