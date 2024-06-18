<?php

declare(strict_types=1);

namespace Order\Application\OrderRemoveAllGroupsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class OrderRemoveAllGroupsOrdersInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;

    /**
     * @var Identifier[]
     */
    public readonly array $groupsIdToRemove;
    /**
     * @var Identifier[]
     */
    public readonly array $groupsIdToChangeUserId;
    public readonly Identifier $userIdToSet;
    public readonly string $systemKey;

    public function __construct(UserShared $userSession, ?array $groupsIdToRemove, ?array $groupsIdToChangeUserId, ?string $userId, ?string $systemKey)
    {
        $this->userSession = $userSession;
        $this->groupsIdToRemove = array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            $groupsIdToRemove ?? []
        );
        $this->groupsIdToChangeUserId = array_map(
            fn (string $orderId) => ValueObjectFactory::createIdentifier($orderId),
            $groupsIdToChangeUserId ?? []
        );

        $this->userIdToSet = ValueObjectFactory::createIdentifier($userId);
        $this->systemKey = $systemKey ?? '';
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupsIdToRemove = $validator->validateValueObjectArray($this->groupsIdToRemove);
        $errorListGroupsIdToChangeUserId = $validator->validateValueObjectArray($this->groupsIdToChangeUserId);
        $errorListUserId = $validator->validateValueObject($this->userIdToSet);

        $errorListSystemKey = $validator
            ->setValue($this->systemKey)
            ->notBlank()
            ->notNull()
            ->validate();

        $errorList = [];
        if (!empty($errorListGroupsIdToRemove)) {
            $errorList = ['groups_id_remove' => $errorListGroupsIdToRemove];
        }

        if (!empty($errorListGroupsIdToChangeUserId)) {
            $errorList = ['groups_id_change_user_id' => $errorListGroupsIdToChangeUserId];
        }

        if (!empty($errorListUserId) && !empty($this->groupsIdToChangeUserId)) {
            $errorList = ['user_id_set' => $errorListUserId];
        }

        if (!empty($errorListSystemKey)) {
            $errorList = ['system_key' => $errorListSystemKey];
        }

        return $errorList;
    }
}
