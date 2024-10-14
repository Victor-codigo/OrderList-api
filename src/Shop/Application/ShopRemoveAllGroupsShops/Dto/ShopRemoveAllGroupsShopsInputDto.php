<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemoveAllGroupsShops\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ShopRemoveAllGroupsShopsInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $groupsId;
    public readonly string $systemKey;

    /**
     * @param string[]|null $groupsId
     */
    public function __construct(UserShared $userSession, ?array $groupsId, ?string $systemKey)
    {
        $this->userSession = $userSession;
        $this->groupsId = array_map(
            fn (string $groupId): Identifier => ValueObjectFactory::createIdentifier($groupId),
            $groupsId ?? []
        );
        $this->systemKey = $systemKey ?? '';
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorListGroupsIdBlank = $validator
            ->setValue($this->groupsId)
            ->notBlank()
            ->validate();

        $errorListGroupsId = $validator->validateValueObjectArray($this->groupsId);
        $errorListSystemKey = $validator
            ->setValue($this->systemKey)
            ->notBlank()
            ->validate();

        $errorList = [];
        if (!empty($errorListGroupsIdBlank)) {
            $errorList['groups_id_empty'] = $errorListGroupsIdBlank;
        }

        if (!empty($errorListGroupsId)) {
            $errorList['groups_id'] = $errorListGroupsId;
        }

        if (!empty($errorListSystemKey)) {
            $errorList['system_key'] = $errorListSystemKey;
        }

        return $errorList;
    }
}
