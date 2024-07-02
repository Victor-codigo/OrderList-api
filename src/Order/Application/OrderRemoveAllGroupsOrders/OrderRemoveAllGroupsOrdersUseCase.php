<?php

declare(strict_types=1);

namespace Order\Application\OrderRemoveAllGroupsOrders;

use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationErrorResponseException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationException;
use Common\Adapter\ModuleCommunication\Exception\ModuleCommunicationTokenNotFoundInRequestException;
use Common\Domain\Config\AppConfig;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersInputDto;
use Order\Application\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersOutputDto;
use Order\Application\OrderRemoveAllGroupsOrders\Exception\OrderRemoveAllGroupsOrdersGroupsAdminsRequestException;
use Order\Application\OrderRemoveAllGroupsOrders\Exception\OrderRemoveAllGroupsOrdersSystemKeyException;
use Order\Application\OrderRemove\Exception\OrderRemoveGroupAndUserValidationException;
use Order\Application\OrderRemove\Exception\OrderRemoveOrdersNotFoundException;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersDto;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersOutputDto as OrderRemoveAllGroupsOrdersOutputDtoService;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\OrderRemoveAllGroupsOrdersService;

class OrderRemoveAllGroupsOrdersUseCase extends ServiceBase
{
    public function __construct(
        private OrderRemoveAllGroupsOrdersService $orderRemoveAllGroupsOrdersService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    /**
     * @throws OrderRemoveOrdersNotFoundException
     * @throws OrderRemoveGroupAndUserValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(OrderRemoveAllGroupsOrdersInputDto $input): OrderRemoveAllGroupsOrdersOutputDto
    {
        $this->validation($input);

        try {
            foreach ($this->getGroupsAdmins($input->groupsIdToChangeUserId) as $groupsIdAdmins) {
                $groupsIdAdminsOnePerGroup = $this->reduceGroupsAdminsToOnlyOneAdmin($groupsIdAdmins);
                $ordersIdRemovedAndUserIdChanged = $this->orderRemoveAllGroupsOrdersService->__invoke(
                    $this->createOrderRemoveAllGroupsOrdersDto($input->groupsIdToRemove, $groupsIdAdminsOnePerGroup)
                );
            }

            return $this->createOrderRemoveAllGroupsOrdersOutputDto($ordersIdRemovedAndUserIdChanged);
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(OrderRemoveAllGroupsOrdersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($input->systemKey !== $this->systemKey) {
            throw OrderRemoveAllGroupsOrdersSystemKeyException::fromMessage('Wrong system key');
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
        } catch (ModuleCommunicationException|\ValueError|ModuleCommunicationTokenNotFoundInRequestException|ModuleCommunicationErrorResponseException|\InvalidArgumentException) {
            throw OrderRemoveAllGroupsOrdersGroupsAdminsRequestException::fromMessage('Error getting groups admins');
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

    private function createOrderRemoveAllGroupsOrdersDto(array $groupsIdToRemove, array $groupsIdToChangeUserId): OrderRemoveAllGroupsOrdersDto
    {
        return new OrderRemoveAllGroupsOrdersDto($groupsIdToRemove, $groupsIdToChangeUserId);
    }

    private function createOrderRemoveAllGroupsOrdersOutputDto(OrderRemoveAllGroupsOrdersOutputDtoService $ordersIdRemovedAndUserIdChanged): OrderRemoveAllGroupsOrdersOutputDto
    {
        return new OrderRemoveAllGroupsOrdersOutputDto(
            $ordersIdRemovedAndUserIdChanged->ordersIdRemoved,
            $ordersIdRemovedAndUserIdChanged->ordersIdChangedUserId
        );
    }
}
