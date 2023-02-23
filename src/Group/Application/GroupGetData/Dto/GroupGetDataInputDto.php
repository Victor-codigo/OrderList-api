<?php

declare(strict_types=1);

namespace Group\Application\GroupGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupGetDataInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    /**
     * @var Identifier[]
     */
    public readonly array $groupsId;

    public function __construct(User $userSession, array|null $groupsId)
    {
        $this->userSession = $userSession;
        $this->groupsId = null === $groupsId ? [] : array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsId
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorListNotEmpty = $validator
            ->setValue($this->groupsId)
            ->notBlank()
            ->validate();

        if (!empty($errorListNotEmpty)) {
            return ['groups_id' => $errorListNotEmpty];
        }

        $errorListGroupId = $validator->validateValueObjectArray($this->groupsId);

        return empty($errorListGroupId) ? [] : ['groups_id' => $errorListGroupId[0]];
    }
}
