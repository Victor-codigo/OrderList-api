<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetData\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersGetDataRequestDto implements RequestDtoInterface
{
    private const LIST_ORDERS_IDS_MAX = AppConfig::ENDPOINT_LIST_ORDERS_GET_DATA_MAX;

    /**
     * @var string[]|null
     */
    public readonly array|null $listOrdersIds;
    public readonly string|null $groupId;
    public readonly string|null $listOrdersNameStartsWith;

    public function __construct(Request $request)
    {
        $this->listOrdersIds = $this->removeListOrdersIdsOverflow($request->query->get('list_orders_ids'));
        $this->groupId = $request->query->get('group_id');
        $this->listOrdersNameStartsWith = $request->query->get('list_orders_name_starts_with');
    }

    private function removeListOrdersIdsOverflow(string|null $listOrdersId): array|null
    {
        if (null === $listOrdersId) {
            return null;
        }

        $listOrdersIdValid = explode(',', $listOrdersId, self::LIST_ORDERS_IDS_MAX + 1);

        if (count($listOrdersIdValid) > self::LIST_ORDERS_IDS_MAX) {
            $listOrdersIdValid = array_slice($listOrdersIdValid, 0, self::LIST_ORDERS_IDS_MAX);
        }

        return $listOrdersIdValid;
    }
}
