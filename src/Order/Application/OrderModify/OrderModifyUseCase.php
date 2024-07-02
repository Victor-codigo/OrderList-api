<?php

declare(strict_types=1);

namespace Order\Application\OrderModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Order\Application\OrderModify\Dto\OrderModifyInputDto;
use Order\Application\OrderModify\Dto\OrderModifyOutputDto;
use Order\Application\OrderModify\Exception\OrderModifyGroupAndUserValidationException;
use Order\Application\OrderModify\Exception\OrderModifyListOrdersIdNotFoundException;
use Order\Application\OrderModify\Exception\OrderModifyOrderIdNotFoundException;
use Order\Application\OrderModify\Exception\OrderModifyProductIdNotFoundException;
use Order\Application\OrderModify\Exception\OrderModifyProductShopRepeatedException;
use Order\Application\OrderModify\Exception\OrderModifyShopIdNotFoundException;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderModify\Dto\OrderModifyDto;
use Order\Domain\Service\OrderModify\Exception\OrderModifyListOrdersIdNotFoundException as OrderModifyServiceListOrdersIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductIdNotFoundException as OrderModifyServiceProductIdNotFoundException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyProductShopRepeatedException as OrderModifyServiceProductShopRepeatedException;
use Order\Domain\Service\OrderModify\Exception\OrderModifyShopIdNotFoundException as OrderModifyServiceShopIdNotFoundException;
use Order\Domain\Service\OrderModify\OrderModifyService;

class OrderModifyUseCase extends ServiceBase
{
    public function __construct(
        private OrderModifyService $orderModifyService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(OrderModifyInputDto $input): OrderModifyOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $orderModified = $this->orderModifyService->__invoke(
                $this->createOrderModifyDto($input)
            );

            return $this->createOrderModifyOutputDto($orderModified);
        } catch (OrderModifyServiceProductIdNotFoundException) {
            throw OrderModifyProductIdNotFoundException::fromMessage('Product not found');
        } catch (OrderModifyServiceShopIdNotFoundException) {
            throw OrderModifyShopIdNotFoundException::fromMessage('Shop not found, or product is not in the shop');
        } catch (OrderModifyServiceListOrdersIdNotFoundException) {
            throw OrderModifyListOrdersIdNotFoundException::fromMessage('List orders not found');
        } catch (OrderModifyServiceProductShopRepeatedException) {
            throw OrderModifyProductShopRepeatedException::fromMessage('Product and shop are already in the order list');
        } catch (DBNotFoundException) {
            throw OrderModifyOrderIdNotFoundException::fromMessage('Order not found');
        } catch (ValidateGroupAndUserException) {
            throw OrderModifyGroupAndUserValidationException::fromMessage('You not belong to the group');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(OrderModifyInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createOrderModifyDto(OrderModifyInputDto $input): OrderModifyDto
    {
        return new OrderModifyDto(
            $input->orderId,
            $input->groupId,
            $input->listOrdersId,
            $input->productId,
            $input->shopId,
            $input->userSession->getId(),
            $input->description,
            $input->amount,
        );
    }

    private function createOrderModifyOutputDto(Order $order): OrderModifyOutputDto
    {
        return new OrderModifyOutputDto($order);
    }
}
