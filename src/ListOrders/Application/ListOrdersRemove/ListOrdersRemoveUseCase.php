<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrdersRemove\Dto\ListOrdersRemoveInputDto;
use ListOrders\Application\ListOrdersRemove\Dto\ListOrdersRemoveOutputDto;
use ListOrders\Application\ListOrdersRemove\Exception\ListOrdersListOrdersNotFoundException;
use ListOrders\Application\ListOrdersRemove\Exception\ListOrdersValidationGroupAndUserException;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Service\ListOrdersRemove\Dto\ListOrdersRemoveDto;
use ListOrders\Domain\Service\ListOrdersRemove\ListOrdersRemoveService;

class ListOrdersRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersRemoveService $listOrdersRemoveService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ListOrdersValidationGroupAndUserException
     * @throws ListOrdersListOrdersNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrdersRemoveInputDto $input): ListOrdersRemoveOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $listOrdersRemoved = $this->listOrdersRemoveService->__invoke(
                $this->createListOrdersRemoveDto($input)
            );

            return $this->createListOrdersRemoveOutputDto($listOrdersRemoved);
        } catch (ValidateGroupAndUserException) {
            throw ListOrdersValidationGroupAndUserException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw ListOrdersListOrdersNotFoundException::fromMessage('List orders not found');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrdersRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createListOrdersRemoveDto(ListOrdersRemoveInputDto $input): ListOrdersRemoveDto
    {
        return new ListOrdersRemoveDto($input->groupId, $input->listsOrdersId);
    }

    /**
     * @param ListOrders[] $listsOrders
     */
    private function createListOrdersRemoveOutputDto(array $listsOrders): ListOrdersRemoveOutputDto
    {
        return new ListOrdersRemoveOutputDto($listsOrders);
    }
}
