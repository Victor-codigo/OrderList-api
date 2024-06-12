<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemoveAllUserGroups\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupRemoveAllUserGroupsRequestDto implements RequestDtoInterface
{
    public function __construct(Request $request)
    {
    }
}
