<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersCreate\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ShareListOrdersCreateOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public Identifier $sharedRecourseId,
    ) {
    }

    /**
     * @return array{ shared_recourse_id: string }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'shared_recourse_id' => $this->sharedRecourseId->getValue(),
        ];
    }
}
