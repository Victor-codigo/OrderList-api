<?php

declare(strict_types=1);

namespace User\Domain\Service\UserCreateGroup;

use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Validation\Group\GROUP_TYPE;
use User\Domain\Service\UserCreateGroup\Dto\UserCreateGroupDto;
use User\Domain\Service\UserCreateGroup\Exception\UserCreateGroupUserException;

class UserCreateGroupService
{
    public function __construct(
        private ModuleCommunicationInterface $moduleCommunication
    ) {
    }

    /**
     * @throws UserCreateGroupUserException
     */
    public function __invoke(UserCreateGroupDto $input): void
    {
        $groupName = $this->generateGroupName($input->userName);
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::groupCreate(
                ValueObjectFactory::createNameWithSpaces($groupName),
                ValueObjectFactory::createDescription(null),
                GROUP_TYPE::USER,
                [],
                false
            )
        );

        if (RESPONSE_STATUS::OK !== $response->status || !empty($response->errors)) {
            throw UserCreateGroupUserException::fromMessage('It was not possible to create the user group');
        }
    }

    private function generateGroupName(NameWithSpaces $userName): string
    {
        return $userName->getValue().mt_rand(100000, 999999);
    }
}
