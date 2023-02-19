<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupRemoveRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
    }
}
