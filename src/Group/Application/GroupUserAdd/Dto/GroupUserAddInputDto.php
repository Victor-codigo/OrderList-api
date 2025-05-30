<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Dto;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Security\UserShared;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\Common\VALIDATION_ERRORS;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\ValidationInterface;

class GroupUserAddInputDto implements ServiceInputDtoInterface
{
    public readonly UserShared $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]|NameWithSpaces[]
     */
    public readonly array $users;
    public readonly Rol $rol;

    /**
     * @param string[] $users
     */
    public function __construct(UserShared $userSession, ?string $groupId, ?array $users, ?string $identifierType, ?bool $admin)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->rol = ValueObjectFactory::createRol($admin ? GROUP_ROLES::ADMIN : GROUP_ROLES::USER);

        $this->users = null === $users ? [] : array_map(
            fn (string $user): NameWithSpaces|Identifier => 'name' === $identifierType
                ? ValueObjectFactory::createNameWithSpaces($user)
                : ValueObjectFactory::createIdentifier($user),
            $users
        );
    }

    /**
     * @return array{}|array<int|string, VALIDATION_ERRORS[]>
     */
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator->validateValueObjectArray([
            'group_id' => $this->groupId,
            'admin' => $this->rol,
        ]);

        $usersIdErrorListNotBlank = $validator
            ->setValue($this->users)
            ->notBlank()
            ->validate();

        $usersIdErrorListValueObject = $validator->validateValueObjectArray($this->users);

        // flat user errors
        $usersIdErrorList = [];
        array_walk_recursive($usersIdErrorListValueObject, function ($error) use (&$usersIdErrorList): void { $usersIdErrorList[] = $error; });
        $usersIdErrorList = array_merge($usersIdErrorListNotBlank, $usersIdErrorList);
        $usersIdErrorList = array_unique($usersIdErrorList, SORT_REGULAR);

        if (!empty($usersIdErrorList)) {
            $errorList['users'] = $usersIdErrorList;
        }

        return $errorList;
    }
}
