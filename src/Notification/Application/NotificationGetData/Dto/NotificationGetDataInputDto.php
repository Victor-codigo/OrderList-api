<?php

declare(strict_types=1);

namespace Notification\Application\NotificationGetData\Dto;

use Override;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class NotificationGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;
    public readonly Language $lang;

    public function __construct(UserShared $userSession, int|null $page, int|null $pageItems, string|null $lang)
    {
        $this->userSession = $userSession;
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
        $this->lang = ValueObjectFactory::createLanguage($lang);
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'page' => $this->page,
            'page_items' => $this->pageItems,
            'lang' => $this->lang,
        ]);
    }
}
