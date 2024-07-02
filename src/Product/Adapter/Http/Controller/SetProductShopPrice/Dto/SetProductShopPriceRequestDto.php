<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\SetProductShopPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class SetProductShopPriceRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int PRODUCTS_SHOPS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_PATCH_PRICES_SHOPS_MAX;

    public readonly string|null $groupId;
    public readonly string|null $productId;
    public readonly string|null $shopId;
    /**
     * @var string[]|null
     */
    public readonly array|null $productsOrShopsId;
    /**
     * @var float[]|null
     */
    public readonly array|null $prices;

    /**
     * @var string[]|null
     */
    public readonly array|null $units;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->productId = $request->request->get('product_id');
        $this->shopId = $request->request->get('shop_id');
        $this->productsOrShopsId = $this->validateArrayOverflow($request->request->all('products_or_shops_id'), self::PRODUCTS_SHOPS_NUM_MAX);
        $this->prices = $this->arrayFilterFloat($request->request->all('prices'), self::PRODUCTS_SHOPS_NUM_MAX);
        $this->units = $this->validateArrayOverflow($request->request->all('units'), self::PRODUCTS_SHOPS_NUM_MAX);
    }
}
