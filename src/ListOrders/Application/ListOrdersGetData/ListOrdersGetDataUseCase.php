<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataInputDto;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataOutputDto;
use ListOrders\Application\ListOrdersGetData\Exception\ListOrdersGetDataListOrderIdsAndListOrderNameStartsWithAreNullException;
use ListOrders\Application\ListOrdersGetData\Exception\ListOrdersGetDataListOrdersNotFoundException;
use ListOrders\Application\ListOrdersGetData\Exception\ListOrdersGetDataValidateUserAndGroupException;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;
use ListOrders\Domain\Service\ListOrdersGetData\ListOrdersGetDataService;

class ListOrdersGetDataUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersGetDataService $ListOrdersGetDataService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ListOrdersGetDataValidateUserAndGroupException
     * @throws ListOrdersGetDataListOrderIdsAndListOrderNameStartsWithAreNullException
     * @throws ListOrdersGetDataListOrdersNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersGetDataInputDto $input): ListOrdersGetDataOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listsOrdersData = $this->ListOrdersGetDataService->__invoke(
                $this->createListOrdersGetDataDto($input)
            );

            return $this->createListOrdersGetDataOutputDto($listsOrdersData);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersGetDataValidateUserAndGroupException::fromMessage('You not belong to the group');
        } catch (LogicException) {
            throw ListOrdersGetDataListOrderIdsAndListOrderNameStartsWithAreNullException::fromMessage('Both list_order_ids and list_orders_starts_with are null');
        } catch (DBNotFoundException) {
            throw ListOrdersGetDataListOrdersNotFoundException::fromMessage('List orders ids not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersGetDataDto(ListOrdersGetDataInputDto $input): ListOrdersGetDataDto
    {
        return new ListOrdersGetDataDto($input->listOrdersId, $input->groupId, $input->listOrdersNameStartsWith);
    }

    private function createListOrdersGetDataOutputDto(array $listsOrdersData): ListOrdersGetDataOutputDto
    {
        return new ListOrdersGetDataOutputDto($listsOrdersData);
    }
}
