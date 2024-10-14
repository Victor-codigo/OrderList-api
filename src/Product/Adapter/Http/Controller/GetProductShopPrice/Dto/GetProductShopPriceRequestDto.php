<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\GetProductShopPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GetProductShopPriceRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int PRODUCTS_MAX = AppConfig::ENDPOINT_PRODUCT_GET_PRODUCTS_MAX;
    private const int SHOPS_MAX = AppConfig::ENDPOINT_PRODUCT_GET_SHOPS_MAX;

    /**
     * @var string[]|null
     */
    public readonly ?array $productsId;
    /**
     * @var string[]|null
     */
    public readonly ?array $shopsId;
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->productsId = $this->validateCsvOverflow($request->query->get('products_id'), self::PRODUCTS_MAX);
        $this->shopsId = $this->validateCsvOverflow($request->query->get('shops_id'), self::SHOPS_MAX);
        $this->groupId = $request->query->get('group_id');
    }
}
