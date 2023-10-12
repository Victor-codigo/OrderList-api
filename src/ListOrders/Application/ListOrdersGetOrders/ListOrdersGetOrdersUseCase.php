<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetOrders;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetOrders\Dto\ListOrdersGetOrdersInputDto;
use ListOrders\Application\ListOrdersGetOrders\Dto\ListOrdersGetOrdersOutputDto;
use ListOrders\Application\ListOrdersGetOrders\Exception\ListOrderGetOrdersNotFound;
use ListOrders\Application\ListOrdersGetOrders\Exception\ListOrdersGetOrdersValidateUserAndGroupException;
use ListOrders\Domain\Service\ListOrdersGetOrders\Dto\ListOrdersGetOrdersDto;
use ListOrders\Domain\Service\ListOrdersGetOrders\ListOrdersGetOrdersService;

class ListOrdersGetOrdersUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersGetOrdersService $listOrdersGetOrdersService,
        private ValidateGroupAndUserService $validateGroupAndUserService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(ListOrdersGetOrdersInputDto $input): ListOrdersGetOrdersOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);
            $listOrderOrdersData = $this->listOrdersGetOrdersService->__invoke(
                $this->createListOrdersGetOrdersDto($input)
            );

            return $this->createListOrdersGetOrdersOutputDto($listOrderOrdersData);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersGetOrdersValidateUserAndGroupException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw ListOrderGetOrdersNotFound::fromMessage('Cannot find the list order');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ListOrdersGetOrdersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersGetOrdersDto(ListOrdersGetOrdersInputDto $input): ListOrdersGetOrdersDto
    {
        return new ListOrdersGetOrdersDto($input->listOrderId, $input->groupId, $input->page, $input->pageItems);
    }

    private function createListOrdersGetOrdersOutputDto(array $listOrderOrdersData): ListOrdersGetOrdersOutputDto
    {
        return new ListOrdersGetOrdersOutputDto($listOrderOrdersData);
    }
}
