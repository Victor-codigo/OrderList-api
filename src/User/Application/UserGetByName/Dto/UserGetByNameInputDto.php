<?php

declare(strict_types=1);

namespace User\Application\UserGetByName\Dto;

use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use User\Domain\Model\User;

class UserGetByNameInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    /**
     * @var NameWithSpaces[]|null
     */
    public readonly ?array $usersName;

    public function __construct(User $userSession, ?array $usersName)
    {
        $this->userSession = $userSession;
        $this->usersName = null === $usersName ? null : array_map(
            fn (string $userName) => ValueObjectFactory::createNameWithSpaces($userName)->slashesToWiteSpaces(),
            $usersName
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator
            ->setValue($this->usersName)
            ->notNull()
            ->notBlank()
            ->validate();

        if (!empty($errorList)) {
            return ['users_name' => $errorList];
        }

        $errorList = $validator->validateValueObjectArray($this->usersName);

        return empty($errorList) ? [] : ['users_name' => $errorList[0]];
    }
}
