<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrdersRemoveRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupId;
    /**
     * @var string[]|null
     */
    public readonly ?array $listsOrdersId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->listsOrdersId = $request->request->all('lists_orders_id');
    }
}
