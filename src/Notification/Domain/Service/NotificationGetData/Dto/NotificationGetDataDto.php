<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationGetData\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Language;

class NotificationGetDataDto
{
    public function __construct(
        public readonly Identifier $userId,
        public readonly PaginatorPage $page,
        public readonly PaginatorPageItems $pageItems,
        public readonly Language $lang,
    ) {
    }
}
