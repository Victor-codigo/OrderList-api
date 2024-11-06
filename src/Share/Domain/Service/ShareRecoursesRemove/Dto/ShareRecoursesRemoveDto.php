<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareRecoursesRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ShareRecoursesRemoveDto
{
    /**
     * @param Identifier[] $RecursesId
     */
    public function __construct(
        public array $RecursesId,
    ) {
    }
}
