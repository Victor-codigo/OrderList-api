<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Common\Adapter\Http\RequestDataValidation\RequestDataValidation;
use Common\Domain\Config\AppConfig;
use Symfony\Component\HttpFoundation\Request;

class GroupUserRemoveRequestDto implements RequestDtoInterface
{
    use RequestDataValidation;

    private const int USERS_NUM_MAX = AppConfig::ENDPOINT_GROUP_USER_REMOVE_MAX_USERS;

    public ?string $groupId;
    /**
     * @var string[]|null
     */
    public ?array $usersId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->usersId = $this->validateArrayOverflow($request->request->all('users_id'), self::USERS_NUM_MAX);
    }
}
