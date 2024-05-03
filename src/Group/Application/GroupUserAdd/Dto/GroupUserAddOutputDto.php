<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class GroupUserAddOutputDto implements ApplicationOutputInterface
{
    /**
     * @var Identifier[]
     */
    public function __construct(
        public readonly array $usersId
    ) {
    }

    public function toArray(): array
    {
        return array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $this->usersId
        );
    }
}
