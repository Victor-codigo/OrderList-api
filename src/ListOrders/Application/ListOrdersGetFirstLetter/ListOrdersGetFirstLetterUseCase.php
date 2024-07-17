<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterInputDto;
use ListOrders\Application\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterOutputDto;
use ListOrders\Application\ListOrdersGetFirstLetter\Exception\ListOrdersGetFirstLetterListOrdersNotFoundException;
use ListOrders\Application\ListOrdersGetFirstLetter\Exception\ListOrdersGetFirstLetterValidateGroupAndUserException;
use ListOrders\Domain\Service\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterDto;
use ListOrders\Domain\Service\ListOrdersGetFirstLetter\ListOrdersGetFirstLetterService;

class ListOrdersGetFirstLetterUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersGetFirstLetterService $listOrdersGetFirstLetterService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ListOrdersGetFirstLetterInputDto $input): ListOrdersGetFirstLetterOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersFirstLetter = $this->listOrdersGetFirstLetterService->__invoke(
                $this->createListOrdersGetFirstLetterDto($input)
            );

            return $this->createListOrdersGetFirstLetterOutputDto($listOrdersFirstLetter);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersGetFirstLetterValidateGroupAndUserException::fromMessage('You have not permissions');
        } catch (DBNotFoundException) {
            throw ListOrdersGetFirstLetterListOrdersNotFoundException::fromMessage('No list orders found');
        } catch (\Throwable) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ListOrdersGetFirstLetterInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersGetFirstLetterDto(ListOrdersGetFirstLetterInputDto $input): ListOrdersGetFirstLetterDto
    {
        return new ListOrdersGetFirstLetterDto($input->groupId);
    }

    private function createListOrdersGetFirstLetterOutputDto(array $listOrdersFirstLetter): ListOrdersGetFirstLetterOutputDto
    {
        return new ListOrdersGetFirstLetterOutputDto($listOrdersFirstLetter);
    }
}
