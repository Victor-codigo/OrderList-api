<?php

declare(strict_types=1);

namespace Group\Application\GroupUserGetGroups\Dto;

use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class GroupUserGetGroupsInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;

    public function __construct(User $userSession)
    {
        $this->userSession = $userSession;
    }

    public function validate(ValidationInterface $validator): array
    {
        return [];
    }
}
