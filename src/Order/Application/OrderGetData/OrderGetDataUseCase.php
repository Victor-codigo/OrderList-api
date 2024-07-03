<?php

declare(strict_types=1);

namespace Order\Application\OrderGetData;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderGetData\Dto\OrderGetDataInputDto;
use Order\Application\OrderGetData\Dto\OrderGetDataOutputDto;
use Order\Application\OrderGetData\Exception\OrderGetDataOrdersNotFoundException;
use Order\Application\OrderGetData\Exception\OrderGetDataValidateGroupAndUserException;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Order\Domain\Service\OrderGetData\OrderGetDataService;

class OrderGetDataUseCase extends ServiceBase
{
    public function __construct(
        private OrderGetDataService $orderGetDataService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws OrderGetDataValidateGroupAndUserException
     * @throws OrderGetDataOrdersNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(OrderGetDataInputDto $input): OrderGetDataOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $ordersData = $this->orderGetDataService->__invoke(
                $this->createOrderGetDataDto($input)
            );

            return $this->createOrderGetDataOutputDto($ordersData, $input->page);
        } catch (ValidateGroupAndUserException) {
            throw OrderGetDataValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw OrderGetDataOrdersNotFoundException::fromMessage('Orders not found');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(OrderGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createOrderGetDataDto(OrderGetDataInputDto $input): OrderGetDataDto
    {
        return new OrderGetDataDto(
            $input->groupId,
            $input->listOrdersId,
            $input->ordersId,
            $input->page,
            $input->pageItems,
            $input->orderAsc,
            $input->filterSection,
            $input->filterText,
        );
    }

    private function createOrderGetDataOutputDto(array $ordersData, PaginatorPage $page): OrderGetDataOutputDto
    {
        $pagesTotal = $this->orderGetDataService->getPagesTotal();

        return new OrderGetDataOutputDto($ordersData, $page, $pagesTotal);
    }
}
