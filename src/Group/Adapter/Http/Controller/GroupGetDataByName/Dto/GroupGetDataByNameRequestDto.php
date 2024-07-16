<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupGetDataByName\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupGetDataByNameRequestDto implements RequestDtoInterface
{
    public readonly ?string $groupName;

    public function __construct(Request $request)
    {
        $this->groupName = $request->attributes->get('group_name');
    }
}
