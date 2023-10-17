<?php

declare(strict_types=1);

namespace Group\Application\GroupGetDataByName\Dto;

use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupGetDataByNameInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Name $groupName;

    public function __construct(UserShared $userSession, string|null $groupName)
    {
        $this->userSession = $userSession;
        $this->groupName = ValueObjectFactory::createName($groupName);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray([
            'group_name' => $this->groupName,
        ]);
    }
}
