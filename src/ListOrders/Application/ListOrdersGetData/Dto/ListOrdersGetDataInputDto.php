<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ListOrdersGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $listOrdersId;
    public readonly NameWithSpaces $listOrdersNameStartsWith;
    public readonly Identifier $groupId;

    public function __construct(UserShared $userShared, array|null $listOrdersIds, string|null $groupId, string|null $listOrdersNamesStartsWith)
    {
        $this->userSession = $userShared;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->listOrdersNameStartsWith = ValueObjectFactory::createNameWithSpaces($listOrdersNamesStartsWith);
        $this->listOrdersId = array_map(
            fn (string $listOrderId) => ValueObjectFactory::createIdentifier($listOrderId),
            $listOrdersIds ?? []
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupId = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorListListOrdersIdsAndListOrdersNameStartsWith = $this->validateListOrdersIdAndListOrdersIdNameStartsWith($validator);

        return array_merge(
            $errorListGroupId,
            $errorListListOrdersIdsAndListOrdersNameStartsWith
        );
    }

    private function validateListOrdersIdAndListOrdersIdNameStartsWith(ValidationInterface $validator): array
    {
        $errorListListOrdersId = [];
        $errorListListOrdersNameStartsWith = [];
        $errorListNameStartsWithAndListOrdersIdAreEmpty = [];
        if (empty($this->listOrdersId) && $this->listOrdersNameStartsWith->isNull()) {
            $errorListNameStartsWithAndListOrdersIdAreEmpty = $validator
                ->setValue($this->listOrdersId)
                ->notBlank()
                ->validate();

            if (!empty($errorListNameStartsWithAndListOrdersIdAreEmpty)) {
                $errorListNameStartsWithAndListOrdersIdAreEmpty = ['list_orders_ids_and_name_starts_with_empty' => $errorListNameStartsWithAndListOrdersIdAreEmpty];
            }
        }

        if (!empty($this->listOrdersId)) {
            $errorListListOrdersId = $validator->validateValueObjectArray($this->listOrdersId);

            if (!empty($errorListListOrdersId)) {
                $errorListListOrdersId = ['list_orders_ids' => $errorListListOrdersId];
            }
        }

        if (!$this->listOrdersNameStartsWith->isNull()) {
            $errorListListOrdersNameStartsWith = $validator->validateValueObject($this->listOrdersNameStartsWith);

            if (!empty($errorListListOrdersNameStartsWith)) {
                $errorListListOrdersNameStartsWith = ['list_orders_name_starts_with' => $errorListListOrdersNameStartsWith];
            }
        }

        return array_merge(
            $errorListNameStartsWithAndListOrdersIdAreEmpty,
            $errorListListOrdersId,
            $errorListListOrdersNameStartsWith
        );
    }
}
