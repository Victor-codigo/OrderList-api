<?php

declare(strict_types=1);

namespace Notification\Application\NotificationGetData\Dto;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class NotificationGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly PaginatorPage $page;
    public readonly PaginatorPageItems $pageItems;
    public readonly Language $lang;

    public function __construct(User $userSession, int|null $page, int|null $pageItems, string|null $lang)
    {
        $this->userSession = $userSession;
        $this->page = ValueObjectFactory::createPaginatorPage($page);
        $this->pageItems = ValueObjectFactory::createPaginatorPageItems($pageItems);
        $this->lang = ValueObjectFactory::createLanguage($lang);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'page' => $this->page,
            'page_items' => $this->pageItems,
            'lang' => $this->lang,
        ]);
    }
}
