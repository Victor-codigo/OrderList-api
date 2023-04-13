<?php

declare(strict_types=1);

namespace Group\Application\GroupGetAdmins\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupGetAdminsInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;

    public function __construct(User $userSession, string|null $groupId)
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
