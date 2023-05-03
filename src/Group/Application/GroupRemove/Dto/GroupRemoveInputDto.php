<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $groupId;
    public readonly UserShared $userSession;

    public function __construct(UserShared $userSession, string|null $groupId)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
        ]);
    }
}
