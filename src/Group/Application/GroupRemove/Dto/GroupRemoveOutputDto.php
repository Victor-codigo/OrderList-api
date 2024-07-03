<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $groupsRemovedId
     */
    public function __construct(
        public readonly array $groupsRemovedId
    ) {
    }

    /**
     * @return string[]
     */
    #[Override]
    public function toArray(): array
    {
        return array_map(
            fn (Identifier $groupId) => $groupId->getValue(),
            $this->groupsRemovedId
        );
    }
}
