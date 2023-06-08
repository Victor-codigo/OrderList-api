<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersAddOrder\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersAddOrderRequestDto implements RequestDtoInterface
{
    private const ORDERS_MAX = AppConfig::ENDPOINT_LIST_ORDERS_ADD_ORDERS_MAX;

    public readonly string|null $listOrdersId;
    public readonly string|null $groupId;
    public readonly array|null $orders;

    public function __construct(Request $request)
    {
        $this->listOrdersId = $request->request->get('list_orders_id');
        $this->groupId = $request->request->get('group_id');
        $this->orders = $this->removeUsersOverflow($request->request->all('orders'));
    }

    private function removeUsersOverflow(array|null $orders): array|null
    {
        if (empty($orders)) {
            return [];
        }

        if (count($orders) > self::ORDERS_MAX) {
            return array_slice($orders, 0, self::ORDERS_MAX);
        }

        return $orders;
    }
}
