<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopRemoveAllGroupsShops\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class ShopRemoveAllGroupsShopsRequestDto implements RequestDtoInterface
{
    /**
     * @var string[]|null
     */
    public readonly ?array $groupsId;
    public readonly ?string $systemKey;

    public function __construct(Request $request)
    {
        $this->groupsId = $request->request->all('groups_id');
        $this->systemKey = $request->request->get('system_key');
    }
}
