<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrderRemoveAllGroupsListsOrders\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ListOrderRemoveAllGroupsListsOrdersRequestDto implements RequestDtoInterface
{
    /**
     * @var string[]|null
     */
    public readonly ?array $groupsIdToRemove;
    /**
     * @var string[]|null
     */
    public readonly ?array $groupsIdToChangeUserId;
    public readonly ?string $userIdToSet;
    public readonly ?string $systemKey;

    public function __construct(Request $request)
    {
        $this->groupsIdToRemove = $request->request->all('groups_id_remove');
        $this->groupsIdToChangeUserId = $request->request->all('groups_id_change_user_id');
        $this->userIdToSet = $request->request->get('user_id_set');
        $this->systemKey = $request->request->get('system_key');
    }
}
