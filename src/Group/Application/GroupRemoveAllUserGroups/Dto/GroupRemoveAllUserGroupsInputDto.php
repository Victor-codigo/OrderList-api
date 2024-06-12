<?php

declare(strict_types=1);

namespace Group\Application\GroupRemoveAllUserGroups\Dto;

use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GroupRemoveAllUserGroupsInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;

    public function __construct(UserShared $userSession)
    {
        $this->userSession = $userSession;
    }

    public function validate(ValidationInterface $validator): array
    {
        return [];
    }
}
