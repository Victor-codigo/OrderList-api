<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use User\Domain\Model\USER_ROLES;

class PermissionDeniedException extends DomainException
{
    /**
     * @param USER_ROLES[] $grants
     */
    public static function fromMessage(string $message, array $rolesNeeded): static
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => $rol->value,
            $rolesNeeded
        );

        return new static(sprintf('%s. Permissions needed: [%s]', $message, implode(', ', $roles)));
    }
}
