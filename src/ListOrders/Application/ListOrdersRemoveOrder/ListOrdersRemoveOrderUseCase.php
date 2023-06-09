<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveOrder;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderInputDto;
use ListOrders\Application\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderOutputDto;
use ListOrders\Application\ListOrdersRemoveOrder\Exception\ListOrdersRemoveOrderOrdersNotFoundException;
use ListOrders\Application\ListOrdersRemoveOrder\Exception\ListOrdersRemoveOrderValidateUserAndGroupException;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderDto;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\ListOrdersRemoveOrderService;

class ListOrdersRemoveOrderUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersRemoveOrderService $ListOrdersRemoveOrderService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ListOrdersRemoveOrderValidateUserAndGroupException
     * @throws ListOrdersRemoveOrderOrdersNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersRemoveOrderInputDto $input): ListOrdersRemoveOrderOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersOrdersRemoved = $this->ListOrdersRemoveOrderService->__invoke(
                $this->createListOrdersRemoveOrderDto($input)
            );

            return $this->createListOrdersRemoveOrderOutputDto($listOrdersOrdersRemoved);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersRemoveOrderValidateUserAndGroupException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw ListOrdersRemoveOrderOrdersNotFoundException::fromMessage('Orders not found');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersRemoveOrderInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersRemoveOrderDto(ListOrdersRemoveOrderInputDto $input): ListOrdersRemoveOrderDto
    {
        return new ListOrdersRemoveOrderDto($input->listOrdersId, $input->groupId, $input->ordersId);
    }

    private function createListOrdersRemoveOrderOutputDto(array $listOrdersOrdersRemoved): ListOrdersRemoveOrderOutputDto
    {
        return new ListOrdersRemoveOrderOutputDto($listOrdersOrdersRemoved);
    }
}
