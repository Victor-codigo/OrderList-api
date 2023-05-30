<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class OrderGetDataRequestDto implements RequestDtoInterface
{
    private const ORDERS_NUM_MAX = AppConfig::ENDPOINT_ORDER_GET_MAX;

    /**
     * @var string[]|null
     */
    public readonly array|null $ordersId;
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->ordersId = $this->removeOverflow($request->query->get('orders_id'), self::ORDERS_NUM_MAX);
        $this->groupId = $request->query->get('group_id');
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
