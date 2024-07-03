<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use Common\Domain\Validation\User\USER_ROLES;

class PermissionDeniedException extends DomainException
{
    public static function fromMessage(string $message, array $rolesNeeded): static
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => $rol->value,
            $rolesNeeded
        );

        return new static(sprintf('%s. Permissions needed: [%s]', $message, implode(', ', $roles)));
    }
}
