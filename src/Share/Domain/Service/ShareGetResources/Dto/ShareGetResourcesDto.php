<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareGetResources\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ShareGetResourcesDto
{
    /**
     * @param Identifier[] $resourcesId
     */
    public function __construct(
        public array $resourcesId,
    ) {
    }
}
