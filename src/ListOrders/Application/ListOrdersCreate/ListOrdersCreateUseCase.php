<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersCreate;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersCreate\Dto\ListOrdersCreateInputDto;
use ListOrders\Application\ListOrdersCreate\Dto\ListOrdersCreateOutputDto;
use ListOrders\Application\ListOrdersCreate\Exception\ListOrdersCreateNameAlreadyExistsException;
use ListOrders\Application\ListOrdersCreate\Exception\ListOrdersCreateValidateGroupAndUserException;
use ListOrders\Domain\Service\ListOrdersCreate\Dto\ListOrdersCreateDto;
use ListOrders\Domain\Service\ListOrdersCreate\Exception\ListOrdersCreateNameAlreadyExistsInGroupException;
use ListOrders\Domain\Service\ListOrdersCreate\ListOrdersCreateService;

class ListOrdersCreateUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersCreateService $listOrdersCreateService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    /**
     * @throws ListOrdersCreateValidateGroupAndUserException
     * @throws ListOrdersCreateNameAlreadyExistsException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersCreateInputDto $input): ListOrdersCreateOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersCreated = $this->listOrdersCreateService->__invoke(
                $this->createListOrdersCreateDto($input)
            );

            return $this->createListOrdersCreateOutputDto($listOrdersCreated->getId());
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersCreateValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (ListOrdersCreateNameAlreadyExistsInGroupException) {
            throw ListOrdersCreateNameAlreadyExistsException::fromMessage('The name already exists');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersCreateDto(ListOrdersCreateInputDto $input): ListOrdersCreateDto
    {
        return new ListOrdersCreateDto(
            $input->groupId,
            $input->userSession->getId(),
            $input->name,
            $input->description,
            $input->dateToBuy
        );
    }

    private function createListOrdersCreateOutputDto(Identifier $listOrdersCreatedId): ListOrdersCreateOutputDto
    {
        return new ListOrdersCreateOutputDto($listOrdersCreatedId);
    }
}
