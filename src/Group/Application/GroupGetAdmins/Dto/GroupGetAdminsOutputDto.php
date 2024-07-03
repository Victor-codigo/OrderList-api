<?php

declare(strict_types=1);

namespace Group\Application\GroupGetAdmins\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class GroupGetAdminsOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly bool $isAdmin,
        public readonly array $admins
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'is_admin' => $this->isAdmin,
            'admins' => $this->admins,
        ];
    }
}
