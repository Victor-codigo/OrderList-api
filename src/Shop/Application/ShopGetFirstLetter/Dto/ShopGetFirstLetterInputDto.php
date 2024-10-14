<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetFirstLetter\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

readonly class ShopGetFirstLetterInputDto implements ServiceInputDtoInterface
{
    public Identifier $groupId;

    public function __construct(?string $groupId)
    {
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);
    }
}
