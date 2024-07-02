<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class ProductRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]
     */
    public readonly array $productsId;
    /**
     * @var Identifier[]
     */
    public readonly array $shopsId;

    /**
     * @param string[]|null $productsId
     * @param string[]|null $shopsId
     */
    public function __construct(UserShared $userSession, string|null $groupId, array|null $productsId, array|null $shopsId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->productsId = array_map(
            fn (string $productId) => ValueObjectFactory::createIdentifier($productId),
            $productsId ?? []
        );
        $this->shopsId = array_map(
            fn (string $shopId) => ValueObjectFactory::createIdentifier($shopId),
            $shopsId ?? []
        );
    }

    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);

        $errorListProductsIdEmpty = $validator
            ->setValue($this->productsId)
            ->notBlank()
            ->notNull()
            ->validate();

        if (!empty($errorListProductsIdEmpty)) {
            $errorList['products_id_empty'] = $errorListProductsIdEmpty;
        }

        $errorListProductsId = $validator->validateValueObjectArray($this->productsId);

        if (!empty($errorListProductsId)) {
            $errorList['products_id'] = $errorListProductsId;
        }

        $errorListShopsId = $validator->validateValueObjectArray($this->shopsId);

        if (!empty($errorListShopsId)) {
            $errorList['shops_id'] = $errorListShopsId;
        }

        return $errorList;
    }
}
