<?php

declare(strict_types=1);

namespace Order\Application\OrderRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderRemove\Dto\OrderRemoveInputDto;
use Order\Application\OrderRemove\Dto\OrderRemoveOutputDto;
use Order\Application\OrderRemove\Exception\OrderRemoveGroupAndUserValidationException;
use Order\Application\OrderRemove\Exception\OrderRemoveOrdersNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderRemove\Dto\OrderRemoveDto;
use Order\Domain\Service\OrderRemove\OrderRemoveService;

class OrderRemoveUseCase extends ServiceBase
{
    public function __construct(
        private OrderRemoveService $orderRemoveService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws OrderRemoveOrdersNotFoundException
     * @throws OrderRemoveGroupAndUserValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(OrderRemoveInputDto $input): OrderRemoveOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $ordersRemoved = $this->orderRemoveService->__invoke(
                $this->createOrderRemoveDto($input)
            );

            return $this->createOrderRemoveOutputDto($ordersRemoved);
        } catch (DBNotFoundException) {
            throw OrderRemoveOrdersNotFoundException::fromMessage('Orders not found');
        } catch (ValidateGroupAndUserException) {
            throw OrderRemoveGroupAndUserValidationException::fromMessage('You not belong to the group');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(OrderRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createOrderRemoveDto(OrderRemoveInputDto $input): OrderRemoveDto
    {
        return new OrderRemoveDto($input->groupId, $input->ordersId);
    }

    /**
     * @param Order[] $ordersRemoved
     */
    private function createOrderRemoveOutputDto(array $ordersRemoved): OrderRemoveOutputDto
    {
        return new OrderRemoveOutputDto($ordersRemoved);
    }
}
