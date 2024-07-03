<?php

declare(strict_types=1);

namespace User\Application\UserRemove\Dto;

use Override;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class UserRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;

    public function __construct(User $userSession)
    {
        $this->userSession = $userSession;
    }

    #[Override]
    public function validate(ValidationInterface $validator): array
    {
        return [];
    }
}
