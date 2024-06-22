<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemoveAllUserGroups\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupRemoveAllUserGroupsRequestDto implements RequestDtoInterface
{
    public readonly ?string $systemKey;

    public function __construct(Request $request)
    {
        $this->systemKey = $request->request->get('system_key');
    }
}
