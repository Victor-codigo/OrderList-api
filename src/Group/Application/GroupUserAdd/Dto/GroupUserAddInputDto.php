<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Dto;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;
use Group\Domain\Model\GROUP_ROLES;
use User\Domain\Model\User;

class GroupUserAddInputDto implements ServiceInputDtoInterface
{
    public readonly User $userSession;
    public readonly Identifier $groupId;
    /**
     * @var Identifier[]|Name[]
     */
    public readonly array $users;
    public readonly Rol $rol;

    /**
     * @param string[] $users
     */
    public function __construct(User $userSession, string|null $groupId, array|null $users, string|null $identifierType, bool|null $admin)
    {
        $this->userSession = $userSession;
        $this->groupId = ValueObjectFactory::createIdentifier($groupId);
        $this->rol = ValueObjectFactory::createRol($admin ? GROUP_ROLES::ADMIN : GROUP_ROLES::USER);

        $this->users = null === $users ? [] : array_map(
            fn (string $user) => 'name' === $identifierType
                ? ValueObjectFactory::createName($user)
                : ValueObjectFactory::createIdentifier($user),
            $users
        );
    }

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
        array_walk_recursive($usersIdErrorListValueObject, function ($error) use (&$usersIdErrorList) { $usersIdErrorList[] = $error; });
        $usersIdErrorList = array_merge($usersIdErrorListNotBlank, $usersIdErrorList);
        $usersIdErrorList = array_unique($usersIdErrorList, SORT_REGULAR);

        if (!empty($usersIdErrorList)) {
            $errorList['users'] = $usersIdErrorList;
        }

        return $errorList;
    }
}
