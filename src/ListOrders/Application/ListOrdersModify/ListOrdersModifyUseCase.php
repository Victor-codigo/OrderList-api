<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersModify;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersModify\Dto\ListOrdersModifyInputDto;
use ListOrders\Application\ListOrdersModify\Dto\ListOrdersModifyOutputDto;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyNameAlreadyExistsException;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyNotFoundException;
use ListOrders\Application\ListOrdersModify\Exception\ListOrdersModifyValidateGroupAndUserException;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Service\ListOrdersModify\Dto\ListOrdersModifyDto;
use ListOrders\Domain\Service\ListOrdersModify\ListOrdersModifyService;

class ListOrdersModifyUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersModifyService $ListOrdersModifyService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ListOrdersModifyNotFoundException
     * @throws ListOrdersModifyNameAlreadyExistsException
     * @throws ListOrdersModifyValidateGroupAndUserException
     */
    public function __invoke(ListOrdersModifyInputDto $input): ListOrdersModifyOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersModified = $this->ListOrdersModifyService->__invoke(
                $this->createListOrdersModifyDto($input)
            );

            return $this->createListOrdersModifyOutputDto($listOrdersModified);
        } catch (DBNotFoundException) {
            throw ListOrdersModifyNotFoundException::fromMessage('List of orders not found');
        } catch (DBUniqueConstraintException) {
            throw ListOrdersModifyNameAlreadyExistsException::fromMessage('The name is already registered');
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersModifyValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ListOrdersModifyInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersModifyDto(ListOrdersModifyInputDto $input): ListOrdersModifyDto
    {
        return new ListOrdersModifyDto($input->userSession->getId(), $input->groupId, $input->listOrdersId, $input->name, $input->description, $input->dateToBuy);
    }

    private function createListOrdersModifyOutputDto(ListOrders $listOrdersModified): ListOrdersModifyOutputDto
    {
        return new ListOrdersModifyOutputDto($listOrdersModified->getId());
    }
}
