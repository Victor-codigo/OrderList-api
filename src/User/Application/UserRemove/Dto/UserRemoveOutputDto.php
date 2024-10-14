<?php

declare(strict_types=1);

namespace User\Application\UserRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class UserRemoveOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Identifier $userId,
    ) {
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->userId->getValue(),
        ];
    }
}
