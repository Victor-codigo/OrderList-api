<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetAdmins\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupGetAdminsRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->attributes->get('group_id');
    }
}
