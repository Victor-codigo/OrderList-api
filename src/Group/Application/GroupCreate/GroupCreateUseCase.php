<?php

declare(strict_types=1);

namespace Group\Application\GroupCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Group\Application\GroupCreate\Dto\GroupCreateInputDto;
use Group\Application\GroupCreate\Exception\GroupCreateCanNotUploadFileException;
use Group\Application\GroupCreate\Exception\GroupCreateNotificationException;
use Group\Application\GroupCreate\Exception\GroupCreateUserGroupTypeAlreadyExitsException as ExceptionGroupCreateUserGroupTypeAlreadyExitsException;
use Group\Application\GroupCreate\Exception\GroupNameAlreadyExistsException;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;
use Group\Domain\Service\GroupCreate\Exception\GroupCreateUserGroupTypeAlreadyExitsException;
use Group\Domain\Service\GroupCreate\GroupCreateService;

class GroupCreateUseCase extends ServiceBase
{
    public function __construct(
        private GroupCreateService $groupCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
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

            $this->createNotificationGroupCreated($input->userCreatorId, $group->getName(), $this->systemKey);

            return $group->getId();
        } catch (DBUniqueConstraintException) {
            throw GroupNameAlreadyExistsException::fromMessage('The group name already exists');
        } catch (GroupCreateUserGroupTypeAlreadyExitsException) {
            throw ExceptionGroupCreateUserGroupTypeAlreadyExitsException::fromMessage('User already has a group of type user');
        } catch (FileUploadException) {
            throw GroupCreateCanNotUploadFileException::fromMessage('An error occurred while file was uploading');
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

    private function createNotificationGroupCreated(Identifier $userId, Name $groupName, string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationCreateGroupCreated($userId, $groupName, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw GroupCreateNotificationException::fromMessage('An error was ocurred when trying to send the notification: user group created');
        }
    }

    private function createGroupCreateDto(GroupCreateInputDto $input): GroupCreateDto
    {
        return new GroupCreateDto(
            $input->userCreatorId,
            $input->name,
            $input->description,
            $input->type,
            $input->image
        );
    }
}
