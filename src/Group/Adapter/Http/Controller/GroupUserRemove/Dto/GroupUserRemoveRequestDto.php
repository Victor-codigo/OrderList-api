<?php

declare(strict_types=1);

namespace Group\Adapter\Http\Controller\GroupUserRemove\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GroupUserRemoveRequestDto implements RequestDtoInterface
{
    private const USERS_NUM_MAX = 50;

    public string|null $groupId;
    /**
     * @var string[]|null
     */
    public array|null $usersId;

    public function __construct(Request $request)
    {
        $this->groupId = $request->request->get('group_id');
        $this->usersId = $this->removeUsersOverflow($request->request->all('users'));
    }

    /**
     * @param string[]|null $usersId
     */
    private function removeUsersOverflow(mixed $usersId): array|null
    {
        if (null === $usersId) {
            return null;
        }

        if (!is_array($usersId)) {
            return null;
        }

        if (count($usersId) > self::USERS_NUM_MAX) {
            return array_slice($usersId, 0, self::USERS_NUM_MAX);
        }

        return $usersId;
    }
}
