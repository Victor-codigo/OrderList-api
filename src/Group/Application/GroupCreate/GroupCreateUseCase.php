<?php

declare(strict_types=1);

namespace Group\Application\GroupCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use Group\Application\GroupCreate\Exception\GroupNameAlreadyExistsException;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;
use Group\Domain\Service\GroupCreate\GroupCreateService;

class GroupCreateUseCase extends ServiceBase
{
    public function __construct(
        private GroupCreateService $groupCreateService,
        private ValidationInterface $validator
        ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainErrorException
     */
    public function __invoke(GroupCreateInputDto $input): Identifier
    {
        $this->validation($input);

        try {
            $group = $this->groupCreateService->__invoke(
                $this->createGroupCreateDto($input)
            );

            return $group->getId();
        } catch (DBUniqueConstraintException) {
            throw GroupNameAlreadyExistsException::fromMessage('The group name already exists');
        } catch (DBConnectionException) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(GroupCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createGroupCreateDto(GroupCreateInputDto $input): GroupCreateDto
    {
        return new GroupCreateDto(
            $input->userCreatorId,
            $input->name,
            $input->description
        );
    }
}
