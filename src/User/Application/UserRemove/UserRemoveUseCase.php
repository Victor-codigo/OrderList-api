<?php

declare(strict_types=1);

namespace User\Application\UserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\Exception\DomainErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Application\UserRemove\Dto\UserRemoveOutputDto;
use User\Application\UserRemove\Exception\UserRemoveGroupsNotFoundException;
use User\Application\UserRemove\Exception\UserRemoveRequestException;
use User\Application\UserRemove\Exception\UserRemoveUserNotFoundException;
use User\Domain\Service\UserRemove\Dto\UserRemoveDto;
use User\Domain\Service\UserRemove\UserRemoveService;

class UserRemoveUseCase extends ServiceBase
{
    public function __construct(
        private UserRemoveService $userRemoveService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(UserRemoveInputDto $userRemoveInputDto): UserRemoveOutputDto
    {
        try {
            $userGroupsRemoved = $this->removeUserGroups($this->systemKey);
        } catch (UserRemoveGroupsNotFoundException) {
            $userGroupsRemoved = [
                'groups_id_removed' => [],
                'groups_id_user_removed' => [],
                'groups_id_user_set_as_admin' => [],
            ];
        } catch (\Exception) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }

        try {
            $this->removeUserNotifications($this->systemKey);
            $this->removeUserGroupsProducts($userGroupsRemoved['groups_id_removed'], $this->systemKey);
            $this->removeUserGroupsShops($userGroupsRemoved['groups_id_removed'], $this->systemKey);
            $this->removeUserGroupsOrdersOrChangeUserId(
                $userGroupsRemoved['groups_id_removed'],
                $userGroupsRemoved['groups_id_user_removed'],
                $this->systemKey
            );
            $this->removeUserGroupsListOrdersOrChangeUserId(
                $userGroupsRemoved['groups_id_removed'],
                $userGroupsRemoved['groups_id_user_removed'],
                $this->systemKey
            );

            $userRemovedId = $this->userRemoveService->__invoke(
                $this->createUserRemoveDto($userRemoveInputDto->userSession->getId())
            );

            return $this->createUserRemoveOutputDto($userRemovedId);
        } catch (DBNotFoundException) {
            throw UserRemoveUserNotFoundException::fromMessage('User not found');
        } catch (\Exception) {
            throw DomainErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @return array{
     *      groups_id_removed: string[],
     *      groups_id_user_removed: string[],
     *      groups_id_user_set_as_admin: array<{
     *          group_id: string
     *          user_id: string
     *      }>
     * }
     *
     * @throws UserRemoveGroupsNotFoundException
     * @throws UserRemoveRequestException
     */
    private function removeUserGroups(string $systemKey): array
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::groupRemoveAllUserGroups($systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            $errors = $response->getErrors();

            if (1 === count($errors) && isset($errors['group_not_found'])) {
                throw UserRemoveGroupsNotFoundException::fromMessage('User groups not found');
            }

            throw UserRemoveRequestException::fromMessage('Error removing user groups');
        }

        return $response->data;
    }

    /**
     * @throws UserRemoveRequestException
     */
    private function removeUserNotifications(string $systemKey): void
    {
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationsRemoveAllUserNotifications($systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            throw UserRemoveRequestException::fromMessage('Error removing user notifications');
        }
    }

    /**
     * @param string[] $groupsId
     *
     * @throws UserRemoveRequestException
     */
    private function removeUserGroupsProducts(array $groupsId, string $systemKey): void
    {
        if (empty($groupsId)) {
            return;
        }

        $groupsIdIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsId
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::productRemoveGroupsProducts($groupsIdIdentifier, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            throw UserRemoveRequestException::fromMessage('Error removing groups products');
        }
    }

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param Identifier[] $groupsIdToRemove
     *
     * @throws UserRemoveRequestException
     */
    private function removeUserGroupsOrdersOrChangeUserId(array $groupsIdToRemove, array $groupsIdToChangeUserId, string $systemKey): void
    {
        if (empty($groupsIdToRemove) && empty($groupsIdToChangeUserId)) {
            return;
        }

        $groupsIdToRemoveIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsIdToRemove
        );

        $groupsIdToChangeUserIdIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsIdToChangeUserId
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::ordersRemoveAllUserOrdersOrChangeUserId(
                $groupsIdToRemoveIdentifier,
                $groupsIdToChangeUserIdIdentifier,
                $systemKey
            )
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            throw UserRemoveRequestException::fromMessage('Error removing groups orders, or changing user id');
        }
    }

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param Identifier[] $groupsIdToRemove
     *
     * @throws UserRemoveRequestException
     */
    private function removeUserGroupsListOrdersOrChangeUserId(array $groupsIdToRemove, array $groupsIdToChangeUserId, string $systemKey): void
    {
        if (empty($groupsIdToRemove) && empty($groupsIdToChangeUserId)) {
            return;
        }

        $groupsIdToRemoveIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsIdToRemove
        );

        $groupsIdToChangeUserIdIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsIdToChangeUserId
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::listOrdersRemoveAllUserListOrdersOrChangeUserId(
                $groupsIdToRemoveIdentifier,
                $groupsIdToChangeUserIdIdentifier,
                $systemKey
            )
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            throw UserRemoveRequestException::fromMessage('Error removing groups list of orders, or changing user id');
        }
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws UserRemoveRequestException
     */
    private function removeUserGroupsShops(array $groupsId, string $systemKey): void
    {
        if (empty($groupsId)) {
            return;
        }

        $groupsIdIdentifier = array_map(
            fn (string $groupId) => ValueObjectFactory::createIdentifier($groupId),
            $groupsId
        );

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::shopRemoveGroupsShops($groupsIdIdentifier, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus() || !empty($response->getErrors())) {
            throw UserRemoveRequestException::fromMessage('Error removing groups shops');
        }
    }

    private function createUserRemoveDto(Identifier $userId): UserRemoveDto
    {
        return new UserRemoveDto($userId);
    }

    private function createUserRemoveOutputDto(Identifier $userId): UserRemoveOutputDto
    {
        return new UserRemoveOutputDto($userId);
    }
}
