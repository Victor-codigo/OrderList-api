<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $listsOrdersId;

    /**
     * @param string[]|null $listsOrdersId
     */
    public function __construct(UserShared $userSession, ?string $groupId, ?array $listsOrdersId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);

        $this->listsOrdersId = array_map(
            fn (string $listOrderId): Identifier => ValueObjectFactory::createIdentifier($listOrderId),
            $listsOrdersId ?? []
        );
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorListListsOrdersId = $this->validateListsOrdersId($validator);

        return array_merge($errorList, $errorListListsOrdersId);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    private function validateListsOrdersId(ValidationInterface $validator): array
    {
        $errorList = [];
        $errorListListsOrdersIdNotEmpty = $validator
            ->setValue($this->listsOrdersId)
            ->notBlank()
            ->notNull()
            ->validate();

        $errorListListsOrdersId = $validator->validateValueObjectArray($this->listsOrdersId);

        if (!empty($errorListListsOrdersIdNotEmpty) || !empty($errorListListsOrdersId)) {
            $errorList['lists_orders_id'] = array_merge($errorListListsOrdersIdNotEmpty, $errorListListsOrdersId);
        }

        return $errorList;
    }
}
