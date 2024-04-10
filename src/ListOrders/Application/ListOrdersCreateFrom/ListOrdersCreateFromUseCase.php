<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersCreateFrom;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersCreateFrom\Dto\ListOrdersCreateFromInputDto;
use ListOrders\Application\ListOrdersCreateFrom\Dto\ListOrdersCreateFromOutputDto;
use ListOrders\Application\ListOrdersCreateFrom\Exception\ListOrdersCreateFromIdNotFoundException;
use ListOrders\Application\ListOrdersCreateFrom\Exception\ListOrdersCreateFromNameAlreadyExistsException;
use ListOrders\Application\ListOrdersCreateFrom\Exception\ListOrdersCreateFromValidateGroupAndUserException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Dto\ListOrdersCreateFromDto;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromListOrdersIdNotFoundException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\Exception\ListOrdersCreateFromNameAlreadyExistsException as ListOrdersCreateFromServiceNameAlreadyExistsException;
use ListOrders\Domain\Service\ListOrdersCreateFrom\ListOrdersCreateFromService;

class ListOrdersCreateFromUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersCreateFromService $listOrdersCreateFromService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ListOrdersCreateFromValidateGroupAndUserException
     * @throws ListOrdersCreateFromNameAlreadyExistsException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersCreateFromInputDto $input): ListOrdersCreateFromOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersCreatedFrom = $this->listOrdersCreateFromService->__invoke(
                $this->createListOrdersCreateFromDto($input)
            );

            return $this->createListOrdersCreateFromOutputDto($listOrdersCreatedFrom->getId());
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersCreateFromValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (ListOrdersCreateFromListOrdersIdNotFoundException) {
            throw ListOrdersCreateFromIdNotFoundException::fromMessage('The list orders id to create from, not found');
        } catch (ListOrdersCreateFromServiceNameAlreadyExistsException) {
            throw ListOrdersCreateFromNameAlreadyExistsException::fromMessage('The name already exists');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersCreateFromInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersCreateFromDto(ListOrdersCreateFromInputDto $input): ListOrdersCreateFromDto
    {
        return new ListOrdersCreateFromDto(
            $input->listOrdersIdCreateFrom,
            $input->groupId,
            $input->userSession->getId(),
            $input->name,
        );
    }

    private function createListOrdersCreateFromOutputDto(Identifier $listOrdersCreatedId): ListOrdersCreateFromOutputDto
    {
        return new ListOrdersCreateFromOutputDto($listOrdersCreatedId);
    }
}
