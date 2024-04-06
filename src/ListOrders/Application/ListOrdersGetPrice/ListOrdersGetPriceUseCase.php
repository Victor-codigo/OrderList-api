<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetPrice\Dto\ListOrdersGetPriceInputDto;
use ListOrders\Application\ListOrdersGetPrice\Dto\ListOrdersGetPriceOutputDto;
use ListOrders\Application\ListOrdersGetPrice\Exception\ListOrdersGetPriceNotFoundException;
use ListOrders\Application\ListOrdersGetPrice\Exception\ListOrdersGetPriceValidateGroupAndUserException;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyNameAlreadyExistsException;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyNotFoundException;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyValidateGroupAndUserException;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceDto;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceOutputDto as ListOrdersGetPriceServiceOutputDto;
use ListOrders\Domain\Service\ListOrdersGetPrice\ListOrdersGetPriceService;

class ListOrdersGetPriceUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersGetPriceService $listOrdersGetPriceService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ListOrdersModifyNotFoundException
     * @throws ListOrdersModifyNameAlreadyExistsException
     * @throws ListOrdersModifyValidateGroupAndUserException
     */
    public function __invoke(ListOrdersGetPriceInputDto $input): ListOrdersGetPriceOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersPrice = $this->listOrdersGetPriceService->__invoke(
                $this->createListOrdersGetPriceDto($input)
            );

            return $this->createListOrdersGetPriceOutputDto($listOrdersPrice);
        } catch (DBNotFoundException) {
            throw ListOrdersGetPriceNotFoundException::fromMessage('List of orders not found');
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersGetPriceValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ListOrdersGetPriceInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersGetPriceDto(ListOrdersGetPriceInputDto $input): ListOrdersGetPriceDto
    {
        return new ListOrdersGetPriceDto($input->listOrdersId, $input->groupId);
    }

    private function createListOrdersGetPriceOutputDto(ListOrdersGetPriceServiceOutputDto $listOrdersPrice): ListOrdersGetPriceOutputDto
    {
        return new ListOrdersGetPriceOutputDto($listOrdersPrice->totalPrice, $listOrdersPrice->boughtPrice);
    }
}
