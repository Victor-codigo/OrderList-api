<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationErrorResponseException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationTokenNotFoundInRequestException;
use Common\Domain\Config\AppConfig;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersInputDto;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersOutputDto;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Exception\ListOrdersRemoveAllGroupsListsOrdersGroupsAdminsRequestException;
use ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Exception\ListOrdersRemoveAllGroupsListsOrdersSystemKeyException;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListOrdersOutputDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\ListOrdersRemoveAllGroupsListsOrdersService;

class ListOrdersRemoveAllGroupsListsOrdersUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersRemoveAllGroupsListsOrdersService $listOrdersRemoveAllGroupsListsOrdersService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    /**
     * @throws listOrdersRemoveListsOrdersNotFoundException
     * @throws listOrdersRemoveGroupAndUserValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersRemoveAllGroupsListsOrdersInputDto $input): ListOrdersRemoveAllGroupsListsOrdersOutputDto
    {
        $this->validation($input);

        try {
            foreach ($this->getGroupsAdmins($input->groupsIdToChangeUserId) as $groupsIdAdmins) {
                $groupsIdAdminsOnePerGroup = $this->reduceGroupsAdminsToOnlyOneAdmin($groupsIdAdmins);
                $listsOrdersIdRemovedAndUserIdChanged = $this->listOrdersRemoveAllGroupsListsOrdersService->__invoke(
                    $this->createListOrdersRemoveAllGroupsListsOrdersDto($input->groupsIdToRemove, $groupsIdAdminsOnePerGroup)
                );
            }

            return $this->createListOrdersRemoveAllGroupsListsOrdersOutputDto($listsOrdersIdRemovedAndUserIdChanged);
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersRemoveAllGroupsListsOrdersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($input->systemKey !== $this->systemKey) {
            throw ListOrdersRemoveAllGroupsListsOrdersSystemKeyException::fromMessage('Wrong system key');
        }
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function getGroupsAdmins(array $groupsId): \Generator
    {
        if (empty($groupsId)) {
            yield [];

            return;
        }

        try {
            $response = $this->moduleCommunication->getAllPagesOfEndpoint(
                ModuleCommunicationFactory::groupGetGroupsAdmins(
                    $groupsId,
                    ValueObjectFactory::createPaginatorPage(1),
                    ValueObjectFactory::createPaginatorPageItems(AppConfig::ENDPOINT_GROUP_GET_GROUPS_ADMINS_MAX)
                ));

            foreach ($response as $responseData) {
                if (empty($responseData)) {
                    yield [];
                    continue;
                }

                yield $responseData['groups'];
            }
        } catch (ModuleCommunicationException|\ValueError|ModuleCommunicationTokenNotFoundInRequestException|ModuleCommunicationErrorResponseException|\InvalidArgumentException $e) {
            throw ListOrdersRemoveAllGroupsListsOrdersGroupsAdminsRequestException::fromMessage('Error getting groups admins');
        }
    }

    private function reduceGroupsAdminsToOnlyOneAdmin(array $groups): array
    {
        $groupsIdAdminsReduced = [];
        foreach ($groups as $groupId => $admins) {
            if (empty($admins)) {
                continue;
            }

            $groupsIdAdminsReduced[] = [
                'group_id' => ValueObjectFactory::createIdentifier($groupId),
                'admin' => ValueObjectFactory::createIdentifier($admins[0]),
            ];
        }

        return $groupsIdAdminsReduced;
    }

    private function createListOrdersRemoveAllGroupsListsOrdersDto(array $groupsIdToRemove, array $groupsIdToChangeUserId): ListOrdersRemoveAllGroupsListsOrdersDto
    {
        return new ListOrdersRemoveAllGroupsListsOrdersDto($groupsIdToRemove, $groupsIdToChangeUserId);
    }

    private function createListOrdersRemoveAllGroupsListsOrdersOutputDto(ListOrdersRemoveAllGroupsListOrdersOutputDto $listsOrdersIdRemovedAndUserIdChanged): ListOrdersRemoveAllGroupsListsOrdersOutputDto
    {
        return new ListOrdersRemoveAllGroupsListsOrdersOutputDto(
            $listsOrdersIdRemovedAndUserIdChanged->listsOrdersIdRemoved,
            $listsOrdersIdRemovedAndUserIdChanged->listsOrdersIdChangedUserId
        );
    }
}
