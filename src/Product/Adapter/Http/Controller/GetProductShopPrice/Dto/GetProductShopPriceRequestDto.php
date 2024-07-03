<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\GetProductShopPrice\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GetProductShopPriceRequestDto implements RequestDtoInterface
{
    private const int PRODUCTS_MAX = AppConfig::ENDPOINT_PRODUCT_GET_PRODUCTS_MAX;
    private const int SHOPS_MAX = AppConfig::ENDPOINT_PRODUCT_GET_SHOPS_MAX;

    public readonly ?array $productsId;
    public readonly ?array $shopsId;
    public readonly ?string $groupId;

    public function __construct(Request $request)
    {
        $this->productsId = $this->removeOverflow($request->query->get('products_id'), self::PRODUCTS_MAX);
        $this->shopsId = $this->removeOverflow($request->query->get('shops_id'), self::SHOPS_MAX);
        $this->groupId = $request->query->get('group_id');
    }

    private function removeOverflow(?string $itemsId, int $numMax): ?array
    {
        if (null === $itemsId) {
            return null;
        }

        $itemsIdValid = explode(',', $itemsId, $numMax + 1);

        if (count($itemsIdValid) > $numMax) {
            $itemsIdValid = array_slice($itemsIdValid, 0, $numMax);
        }

        return $itemsIdValid;
    }
}
