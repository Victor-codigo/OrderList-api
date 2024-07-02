<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserAdd\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupUserAddRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int USERS_NUM_MAX = AppConfig::ENDPOINT_GROUP_USER_ADD_MAX_USERS;

    public readonly ?string $groupId;
    public readonly ?array $users;
    public readonly ?string $identifierType;
    public readonly ?bool $admin;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->users = $this->validateArrayOverflow($request->request->all('users'), self::USERS_NUM_MAX);
        $this->identifierType = $request->request->get('identifier_type');
        $this->admin = $request->request->get('admin');
    }
}
