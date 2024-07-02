<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class OrderRemoveRequestDto implements RequestDtoInterface
{
    private const int ORDERS_MAX = AppConfig::ENDPOINT_ORDER_REMOVE_MAX;

    public readonly array|null $ordersId;
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->ordersId = $this->removeOrdersOverflow($request->request->all('orders_id'));
        $this->groupId = $request->request->get('group_id');
    }

    private function removeOrdersOverflow(array|null $ordersId): array|null
    {
        $ordersIdValid = $ordersId;
        if (count($ordersId) > self::ORDERS_MAX) {
            $ordersIdValid = array_slice($ordersId, 0, self::ORDERS_MAX);
        }

        return $ordersIdValid;
    }
}
