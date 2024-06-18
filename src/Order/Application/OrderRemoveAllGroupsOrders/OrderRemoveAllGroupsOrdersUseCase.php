<?php

declare(strict_types=1);

namespace Order\Application\OrderRemoveAllGroupsOrders;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersInputDto;
use Order\Application\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersOutputDto;
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
            $ordersIdRemovedAndUserIdChanged = $this->orderRemoveAllGroupsOrdersService->__invoke(
                $this->createOrderRemoveAllGroupsOrdersDto($input)
            );

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

    private function createOrderRemoveAllGroupsOrdersDto(OrderRemoveAllGroupsOrdersInputDto $input): OrderRemoveAllGroupsOrdersDto
    {
        return new OrderRemoveAllGroupsOrdersDto($input->groupsIdToRemove, $input->groupsIdToChangeUserId, $input->userIdToSet);
    }

    private function createOrderRemoveAllGroupsOrdersOutputDto(OrderRemoveAllGroupsOrdersOutputDtoService $ordersIdRemovedAndUserIdChanged): OrderRemoveAllGroupsOrdersOutputDto
    {
        return new OrderRemoveAllGroupsOrdersOutputDto(
            $ordersIdRemovedAndUserIdChanged->ordersIdRemoved,
            $ordersIdRemovedAndUserIdChanged->ordersIdChangedUserId
        );
    }
}
