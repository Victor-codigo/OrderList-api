<?php

declare(strict_types=1);

namespace User\Application\UserEmailComfirmation\Dto;

use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class UserEmailConfirmationInputDto implements ServiceInputDtoInterface
{
    public readonly JwtToken|null $token;

    public function __construct(string|null $token)
    {
        $this->token = ValueObjectFactory::createJwtToken($token);
    }

    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray(['token' => $this->token]);
    }
}
