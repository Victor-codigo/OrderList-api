<?php

declare(strict_types=1);

namespace User\Application\UserRegisterEmailConfirmation\Dto;

use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\ValidationInterface;

class UserEmailConfirmationInputDto implements ServiceInputDtoInterface
{
    public readonly ?JwtToken $token;

    public function __construct(?string $token)
    {
        $this->token = ValueObjectFactory::createJwtToken($token);
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        return $validator->validateValueObjectArray(['token' => $this->token]);
    }
}
