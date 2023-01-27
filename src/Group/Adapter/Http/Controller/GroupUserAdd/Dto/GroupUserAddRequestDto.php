<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserAdd\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupUserAddRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    public readonly array|null $usersId;
    public readonly bool|null $admin;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->usersId = $request->request->get('users');
        $this->admin = $request->request->get('admin');
    }
}
