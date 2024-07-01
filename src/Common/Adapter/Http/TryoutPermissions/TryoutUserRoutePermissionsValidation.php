<?php

declare(strict_types=1);

namespace Common\Adapter\Http\TryoutPermissions;

use Common\Adapter\Http\TryoutPermissions\Exception\TryoutUserRoutePermissionsException;
use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\String\Identifier;

class TryoutUserRoutePermissionsValidation
{
    public const ROUTES_NAMES_ALLOWED = AppConfig::USER_GUEST_ROUTES_NAMES_ALLOWED;

    public function __construct(
        private readonly string $userTryoutId
    ) {
    }

    /**
     * @throws TryoutUserRoutePermissionsException
     */
    public function __invoke(Identifier $userIdCurrent, string $currentRouteName): void
    {
        if ($this->userTryoutId !== $userIdCurrent->getValue()) {
            return;
        }

        if (!$this->validateUserTryoutRoute($currentRouteName)) {
            throw TryoutUserRoutePermissionsException::fromMessage('Try out user, has no access to this route');
        }
    }

    private function validateUserTryoutRoute(string $routeName): bool
    {
        return in_array($routeName, self::ROUTES_NAMES_ALLOWED);
    }
}
