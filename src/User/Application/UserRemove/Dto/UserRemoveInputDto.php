<?php

declare(strict_types=1);

namespace User\Application\UserRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class UserRemoveInputDto implements ServiceInputDtoInterface
{
    public readonly Identifier $userId;
    public readonly User $userSession;

    public function __construct(User $userSession, string|null $userId)
    {
        $this->userSession = $userSession;
        $this->userId = ValueObjectFactory::createIdentifier($userId);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray(['id' => $this->userId]);
    }
}
