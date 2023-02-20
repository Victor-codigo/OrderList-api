<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupModify\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupModifyRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly string|null $name;
    public readonly string|null $description;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->name = $request->request->get('name');
        $this->description = $request->request->get('description');
    }
}
