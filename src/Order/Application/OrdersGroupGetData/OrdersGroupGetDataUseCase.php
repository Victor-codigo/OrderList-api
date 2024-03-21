<?php

declare(strict_types=1);

namespace Order\Application\OrdersGroupGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrdersGroupGetData\Dto\OrdersGroupGetDataInputDto;
use Order\Application\OrdersGroupGetData\Dto\OrdersGroupGetDataOutputDto;
use Order\Application\OrdersGroupGetData\Exception\OrdersGroupGetNotFound;
use Order\Application\OrdersGroupGetData\Exception\OrdersGroupGetValidateUserAndGroupException;
use Order\Domain\Service\OrdersGroupGetData\Dto\OrdersGroupGetDataDto;
use Order\Domain\Service\OrdersGroupGetData\OrdersGroupGetDataService;

class OrdersGroupGetDataUseCase extends ServiceBase
{
    public function __construct(
        private OrdersGroupGetDataService $ordersGroupGetDataService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(OrdersGroupGetDataInputDto $input): OrdersGroupGetDataOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);
            $ordersGroupData = $this->ordersGroupGetDataService->__invoke(
                $this->createOrdersGroupGetDataDto($input)
            );
            $paginationTotalPages = $this->ordersGroupGetDataService->getPaginationTotalPages();

            return $this->createOrdersGroupGetDataOutputDto(
                $ordersGroupData,
                $input->page->getValue(),
                $this->ordersGroupGetDataService->getPaginationTotalPages($ordersGroupData, $input->page->getValue(), $paginationTotalPages)
            );
        } catch (ValidateGroupAndUserException) {
            throw OrdersGroupGetValidateUserAndGroupException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw OrdersGroupGetNotFound::fromMessage('Cannot find orders in the group');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(OrdersGroupGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createOrdersGroupGetDataDto(OrdersGroupGetDataInputDto $input): OrdersGroupGetDataDto
    {
        return new OrdersGroupGetDataDto($input->groupId, $input->page, $input->pageItems, $input->orderAsc);
    }

    private function createOrdersGroupGetDataOutputDto(array $ordersGroupData, int $page, int $pagesTotal): OrdersGroupGetDataOutputDto
    {
        return new OrdersGroupGetDataOutputDto($ordersGroupData, $page, $pagesTotal);
    }
}
