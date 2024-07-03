<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
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

            return $this->createListOrdersGetDataOutputDto($listsOrdersData, $input->page, $this->ListOrdersGetDataService->getPagesTotal());
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersGetDataValidateUserAndGroupException::fromMessage('You not belong to the group');
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
        return new ListOrdersGetDataDto(
            $input->groupId,
            $input->listOrdersId,
            $input->orderAsc,
            $input->filterSection,
            $input->filterText,
            $input->page,
            $input->pageItems
        );
    }

    private function createListOrdersGetDataOutputDto(array $listsOrdersData, PaginatorPage $page, int $pagesTotal): ListOrdersGetDataOutputDto
    {
        return new ListOrdersGetDataOutputDto($listsOrdersData, $page, $pagesTotal);
    }
}
