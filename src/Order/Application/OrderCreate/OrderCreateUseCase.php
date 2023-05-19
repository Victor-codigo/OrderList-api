<?php

declare(strict_types=1);

namespace Order\Application\OrderCreate;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderCreate\Dto\OrderCreateInputDto;
use Order\Application\OrderCreate\Dto\OrderCreateOutputDto;
use Order\Application\OrderCreate\Dto\OrderDataDto;
use Order\Application\OrderCreate\Exception\OrderCreateGroupAndUserValidationException;
use Order\Application\OrderCreate\Exception\OrderCreateProductNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderCreate\Dto\OrderCreateDto;
use Order\Domain\Service\OrderCreate\Dto\OrderDataServiceDto;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductNotFoundException as OrderCreateServiceProductNotFoundException;
use Order\Domain\Service\OrderCreate\OrderCreateService;

class OrderCreateUseCase extends ServiceBase
{
    public function __construct(
        private OrderCreateService $OrderCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    public function __invoke(OrderCreateInputDto $input): OrderCreateOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $orders = $this->OrderCreateService->__invoke(
                $this->createOrderCreateDto($input->groupId, $input->ordersData)
            );

            return $this->createOrderCreateOutputDto($orders);
        } catch (ValidateGroupAndUserException) {
            throw OrderCreateGroupAndUserValidationException::fromMessage('You not belongs to the group');
        } catch (OrderCreateServiceProductNotFoundException) {
            throw OrderCreateProductNotFoundException::fromMessage('Product or products not found');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(OrderCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param OrderDataDto[] $ordersData
     * @param Identifier[]   $productsId
     * @param Identifier[]   $shopsId
     */
    private function createOrderCreateDto(Identifier $groupId, array $ordersData): OrderCreateDto
    {
        $ordersServiceData = [];
        foreach ($ordersData as $order) {
            $ordersServiceData[] = new OrderDataServiceDto(
                $order->productId,
                $order->userId,
                $order->shopId,
                $order->description,
                $order->amount
            );
        }

        return new OrderCreateDto($groupId, $ordersServiceData);
    }

    private function createOrderCreateOutputDto(array $orders): OrderCreateOutputDto
    {
        $ordersId = array_map(
            fn (Order $order) => $order->getId()->getValue(),
            $orders
        );

        return new OrderCreateOutputDto($ordersId);
    }
}
