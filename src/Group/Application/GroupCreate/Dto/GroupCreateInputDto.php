<?php

declare(strict_types=1);

namespace Group\Application\GroupCreate\Dto;

use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupCreateInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $userCreatorId;
    public readonly Name $name;
    public readonly Description $description;

    public function __construct(Identifier $userCreatorId, string|null $name, string|null $description)
    {
        $this->userCreatorId = $userCreatorId;
        $this->name = ValueObjectFactory::createName($name);
        $this->description = ValueObjectFactory::createDescription($description);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'name' => $this->name,
            'description' => $this->description,
        ]);
    }
}
