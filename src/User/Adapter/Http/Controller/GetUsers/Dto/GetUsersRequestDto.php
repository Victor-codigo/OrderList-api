<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\GetUsers\Dto;

use Common\Adapter\Http\Dto\RequestDtoInterface;
use Symfony\Component\HttpFoundation\Request;

class GetUsersRequestDto implements RequestDtoInterface
{
    private const int USERS_NUM_MAX = 50;

    /**
     * @var string[]
     */
    public readonly ?array $usersId;

    public function __construct(Request $request)
    {
        $requestUsersId = $request->attributes->get('users_id');
        $this->usersId = $this->removeUsersOverflow($requestUsersId);
    }

    private function removeUsersOverflow(?string $usersId): ?array
    {
        if (null === $usersId) {
            return null;
        }

        $usersIdValid = explode(',', $usersId, self::USERS_NUM_MAX + 1);

        if (count($usersIdValid) > self::USERS_NUM_MAX) {
            $usersIdValid = array_slice($usersIdValid, 0, self::USERS_NUM_MAX);
        }

        return $usersIdValid;
    }
}
