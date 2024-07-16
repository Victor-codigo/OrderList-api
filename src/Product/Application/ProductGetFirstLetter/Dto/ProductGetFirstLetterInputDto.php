<?php

declare(strict_types=1);

namespace Product\Application\ProductGetFirstLetter\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

readonly class ProductGetFirstLetterInputDto implements ServiceInputDtoInterface
{
    public Identifier $groupId;

    public function __construct(?string $groupId)
    {
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);
    }
}
