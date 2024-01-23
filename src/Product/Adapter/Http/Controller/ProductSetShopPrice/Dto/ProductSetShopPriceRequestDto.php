<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductSetShopPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ProductSetShopPriceRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const PRODUCTS_SHOPS_NUM_MAX = AppConfig::ENDPOINT_PRODUCT_PATCH_PRICES_SHOPS_MAX;

    public readonly string|null $groupId;
    /**
     * @var string[]|null
     */
    public readonly array|null $productsId;
    /**
     * @var string[]|null
     */
    public readonly array|null $shopsId;
    /**
     * @var float[]|null
     */
    public readonly array|null $prices;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->productsId = $this->validateArrayOverflow($request->request->all('products_id'), self::PRODUCTS_SHOPS_NUM_MAX);
        $this->shopsId = $this->validateArrayOverflow($request->request->all('shops_id'), self::PRODUCTS_SHOPS_NUM_MAX);
        $this->prices = $this->arrayFilterFloat($request->request->all('prices'), self::PRODUCTS_SHOPS_NUM_MAX);
    }
}
