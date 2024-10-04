<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareListOrdersCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

readonly class ShareListOrderCreateDto
{
    public function __construct(
        public Identifier $listOrdersId,
        public Identifier $userId,
    ) {
    }
}
