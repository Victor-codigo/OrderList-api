<?php

declare(strict_types=1);

namespace User\Application\GetUsers\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class GetUsersInputDto implements ServiceInputDtoInterface
{
    /**
     * @var Identifier[]
     */
    public readonly array|null $usersId;

    private int $numMaxIds;

    public function getNumMaxIds(): int
    {
        return $this->numMaxIds;
    }

    public function __construct(int $numMaxIds, array|null $usersId)
    {
        $this->numMaxIds = $numMaxIds;
        $this->usersId = $this->getUsersIdentifiers($usersId);
    }

    private function getUsersIdentifiers(array|null $usersId): array|null
    {
        if (null === $usersId) {
            return null;
        }

        $usersIdValid = array_slice($usersId, 0, $this->numMaxIds);

        return array_map(
            fn (string $id) => ValueObjectFactory::createIdentifier($id),
            $usersIdValid
        );
    }

    public function validate(ValidationInterface $validator): array
    {
        $errorList = $validator
            ->setValue($this->usersId)
            ->notNull()
            ->notBlank()
            ->validate();

        if (!empty($errorList)) {
            return $errorList;
        }

        return $validator->validateValueObjectArray($this->usersId);
    }
}
