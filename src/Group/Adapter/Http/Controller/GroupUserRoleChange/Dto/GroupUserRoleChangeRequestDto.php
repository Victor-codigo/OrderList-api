<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRoleChange\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupUserRoleChangeRequestDto implements RequestDtoInterface
{
    public readonly string|null $groupId;
    /**
     * @var string[]|null
     */
    public readonly array|null $usersId;
    public readonly bool|null $admin;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->usersId = $request->request->get('users');
        $this->admin = $request->request->get('admin');
    }
}
