<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ShopGetDataRequestDto implements RequestDtoInterface
{
    private const SHOPS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_SHOPS_MAX;
    private const PRODUCTS_NUM_MAX = AppConfig::ENDPOINT_SHOP_GET_PRODUCTS_MAX;

    public readonly string|null $groupId;
    public readonly array|null $shopsId;
    public readonly array|null $productsId;
    public readonly string|null $shopNameStartsWith;

    public function __construct(Request $request)
    {
        $this->groupId = $request->query->get('group_id');
        $this->shopsId = $this->removeOverflow($request->query->get('shops_id'), self::SHOPS_NUM_MAX);
        $this->productsId = $this->removeOverflow($request->query->get('products_id'), self::PRODUCTS_NUM_MAX);
        $this->shopNameStartsWith = $request->query->get('shop_name_starts_with');
    }

    private function removeOverflow(string|null $itemsId, int $numMax): array|null
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
