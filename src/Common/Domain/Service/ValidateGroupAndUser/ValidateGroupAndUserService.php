<?php

declare(strict_types=1);

namespace Common\Domain\Service\ValidateGroupAndUser;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;

class ValidateGroupAndUserService
{
    public function __construct(
        private ModuleCommunicationInterface $moduleCommunication
    ) {
    }

    /**
     * @throws ValidateGroupAndUserException
     * @throws Error400Exception
     * @throws ModuleCommunicationException
     * @throws ValueError
     */
    public function __invoke(Identifier $groupId): void
    {
        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(1);

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::groupGetUsers($groupId, $page, $pageItems)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw ValidateGroupAndUserException::fromMessage('Error validating the group and the user');
        }
    }
}
