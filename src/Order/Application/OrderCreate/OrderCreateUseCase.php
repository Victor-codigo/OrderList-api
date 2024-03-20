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
use Order\Application\OrderCreate\Exception\OrderCreateListOrdersNotFoundException;
use Order\Application\OrderCreate\Exception\OrderCreateProductNotFoundException;
use Order\Application\OrderCreate\Exception\OrderCreateShopNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderCreate\Dto\OrderCreateDto;
use Order\Domain\Service\OrderCreate\Dto\OrderDataServiceDto;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateListOrdersNotFoundException as OrderCreateServiceListOrdersNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductNotFoundException as OrderCreateServiceProductNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateShopNotFoundException as OrderCreateServiceShopNotFoundException;
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
            throw OrderCreateGroupAndUserValidationException::fromMessage('You not belong to the group');
        } catch (OrderCreateServiceListOrdersNotFoundException) {
            throw OrderCreateListOrdersNotFoundException::fromMessage('List of orders or lists of orders not found');
        } catch (OrderCreateServiceProductNotFoundException) {
            throw OrderCreateProductNotFoundException::fromMessage('Product or products not found');
        } catch (OrderCreateServiceShopNotFoundException) {
            throw OrderCreateShopNotFoundException::fromMessage('Shop or shops not found');
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
     */
    private function createOrderCreateDto(Identifier $groupId, array $ordersData): OrderCreateDto
    {
        $ordersServiceData = [];
        foreach ($ordersData as $order) {
            $ordersServiceData[] = new OrderDataServiceDto(
                $order->listOrdersId,
                $order->productId,
                $order->userId,
                $order->shopId,
                $order->description,
                $order->amount,
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
