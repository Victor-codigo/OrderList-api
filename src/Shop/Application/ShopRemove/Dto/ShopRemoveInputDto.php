<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class ShopRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $shopsId;
    public readonly Identifier $groupId;

    /**
     * @param string[]|null $shopsId
     */
    public function __construct(UserShared $userSession, ?string $groupId, ?array $shopsId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->shopsId = array_map(
            fn (string $shopId): Identifier => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );
    }

    /**
     * @return array<string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorListNoShopsId = $validator
            ->setValue($this->shopsId)
            ->notBlank()
            ->validate();

        if (!empty($errorListNoShopsId)) {
            $errorListNoShopsId = ['shops_id_empty' => $errorListNoShopsId];
        }

        $errorListShopsId = $validator->validateValueObjectArray($this->shopsId);
        if (!empty($errorListShopsId)) {
            $errorListShopsId = ['shops_id' => $errorListShopsId];
        }

        $errorListGroupId = $validator->validateValueObject($this->groupId);

        if (!empty($errorListGroupId)) {
            $errorListGroupId = ['group_id' => $errorListGroupId];
        }

        return array_merge($errorListNoShopsId, $errorListShopsId, $errorListGroupId);
    }
}
