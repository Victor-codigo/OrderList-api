<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersAddOrder;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersAddOrder\Dto\ListOrdersAddOrderInputDto;
use ListOrders\Application\ListOrdersAddOrder\Dto\ListOrdersAddOrderOutputDto;
use ListOrders\Application\ListOrdersAddOrder\Exception\ListOrdersAddOrderAllOrdersAreAlreadyInListOrdersException;
use ListOrders\Application\ListOrdersAddOrder\Exception\ListOrdersAddOrderListOrdersNotFoundException;
use ListOrders\Application\ListOrdersAddOrder\Exception\ListOrdersAddOrderValidateUserAndGroupException;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Service\ListOrdersAddOrder\Dto\ListOrdersAddOrderDto;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException as ExceptionListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException;
use ListOrders\Domain\Service\ListOrdersAddOrder\Exception\ListOrdersAddOrdersListOrderNotFoundException;
use ListOrders\Domain\Service\ListOrdersAddOrder\ListOrdersAddOrderService;

class ListOrdersAddOrderUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersAddOrderService $ListOrdersAddOrderService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ListOrdersAddOrderInputDto $input): ListOrdersAddOrderOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersOrdersSaved = $this->ListOrdersAddOrderService->__invoke(
                $this->createListOrdersAddOrderDto($input)
            );

            return $this->createListOrdersAddOrderOutputDto($listOrdersOrdersSaved);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersAddOrderValidateUserAndGroupException::fromMessage('You not belong to the group');
        } catch (ListOrdersAddOrdersListOrderNotFoundException) {
            throw ListOrdersAddOrderListOrdersNotFoundException::fromMessage('list of orders not found');
        } catch (ExceptionListOrdersAddOrderAllOrdersAreAlreadyInTheListOrdersException) {
            throw ListOrdersAddOrderAllOrdersAreAlreadyInListOrdersException::fromMessage('All orders, are already in the list of orders');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersAddOrderInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersAddOrderDto(ListOrdersAddOrderInputDto $input): ListOrdersAddOrderDto
    {
        return new ListOrdersAddOrderDto($input->listOrdersId, $input->groupId, $input->ordersBought);
    }

    /**
     * @param ListOrdersOrders[] $listOrdersOrdersSaved
     */
    private function createListOrdersAddOrderOutputDto(array $listOrdersOrdersSaved): ListOrdersAddOrderOutputDto
    {
        return new ListOrdersAddOrderOutputDto($listOrdersOrdersSaved);
    }
}
