<?php

declare(strict_types=1);

namespace Order\Application\OrderBought;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderBought\Dto\OrderBoughtInputDto;
use Order\Application\OrderBought\Dto\OrderBoughtOutputDto;
use Order\Application\OrderBought\Exception\OrderBoughtGroupAndUserValidationException;
use Order\Application\OrderModify\Exception\OrderModifyOrderIdNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderBought\Dto\OrderBoughtDto;
use Order\Domain\Service\OrderBought\OrderBoughtService;

class OrderBoughtUseCase extends ServiceBase
{
    public function __construct(
        private OrderBoughtService $orderBoughtService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(OrderBoughtInputDto $input): OrderBoughtOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $orderModified = $this->orderBoughtService->__invoke(
                $this->createOrderBoughtDto($input)
            );

            return $this->createOrderBoughtOutputDto($orderModified);
        } catch (ValidateGroupAndUserException) {
            throw OrderBoughtGroupAndUserValidationException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw OrderModifyOrderIdNotFoundException::fromMessage('Order not found');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(OrderBoughtInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createOrderBoughtDto(OrderBoughtInputDto $input): OrderBoughtDto
    {
        return new OrderBoughtDto(
            $input->orderId,
            $input->groupId,
            $input->bought,
        );
    }

    private function createOrderBoughtOutputDto(Order $order): OrderBoughtOutputDto
    {
        return new OrderBoughtOutputDto($order);
    }
}
