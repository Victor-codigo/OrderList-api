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

    private const int SHOPS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_SHOPS_MAX;
    private const int PRODUCTS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_PRODUCTS_MAX;

    public readonly ?string $groupId;
    /**
     * @var string[]|null
     */
    public readonly ?array $shopsId;
    /**
     * @var string[]|null
     */
    public readonly ?array $productsId;
    public readonly ?string $shopNameFilterType;
    public readonly string|float|int|null $shopNameFilterValue;
    public readonly ?string $shopName;
    public readonly ?bool $orderArc;
    public readonly ?int $page;
    public readonly ?int $pageItems;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->shopsId = $this->validateCsvOverflow($request->query->get('shops_id'), self::SHOPS_NUM_MAX);
        $this->productsId = $this->validateCsvOverflow($request->query->get('products_id'), self::PRODUCTS_NUM_MAX);
        $this->shopNameFilterType = $request->query->get('shop_name_filter_type');
        $this->shopNameFilterValue = $request->query->get('shop_name_filter_value');
        $this->shopName = $request->query->get('shop_name');
        $this->orderArc = $request->query->getBoolean('order_asc', true);
        $this->page = $request->query->getInt('page');
        $this->pageItems = $request->query->getInt('page_items');
    }
}
