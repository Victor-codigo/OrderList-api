<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersCreateFrom\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersCreateFromInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $listOrdersIdCreateFrom;
    public readonly Identifier $groupId;
    public readonly NameWithSpaces $name;

    public function __construct(UserShared $userSession, ?string $listOrdersIdCreateFrom, ?string $groupId, ?string $name)
    {
        $this->userSession = $userSession;
        $this->listOrdersIdCreateFrom = ValueObjectFactory::createIdentifier($listOrdersIdCreateFrom);
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->name = ValueObjectFactory::createNameWithSpaces($name);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'list_orders_id_create_from' => $this->listOrdersIdCreateFrom,
            'group_id' => $this->groupId,
            'name' => $this->name,
        ]);
    }
}
