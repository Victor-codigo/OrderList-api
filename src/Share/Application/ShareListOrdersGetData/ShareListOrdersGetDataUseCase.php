<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;
use ListOrders\Domain\Service\ListOrdersGetData\ListOrdersGetDataService;
use Order\Domain\Model\Order;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Order\Domain\Service\OrderGetData\OrderGetDataService;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Share\Application\ShareListOrdersCreate\Exception\ShareCreateListOrdersNotFoundException;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataInputDto;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataOutputDto;
use Share\Domain\Model\Share;
use Share\Domain\Service\ShareGetResources\Dto\ShareGetResourcesDto;
use Share\Domain\Service\ShareGetResources\ShareGetResourcesService;
use Shop\Domain\Model\Shop;

class ShareListOrdersGetDataUseCase extends ServiceBase
{
    public function __construct(
        private ShareGetResourcesService $shareGetResourcesService,
        private ValidationInterface $validator,
        private ListOrdersGetDataService $listOrdersGetDataService,
        private OrderGetDataService $orderGetDataService,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ShareListOrdersGetDataInputDto $input): ShareListOrdersGetDataOutputDto
    {
        $this->validation($input);

        try {
            $sharedListOrders = $this->getSharedListOrders($input->listOrdersId);
            $listOrders = $this->getListOrders($sharedListOrders, $input->page, $input->pageItems);
            $orders = $this->getOrders($listOrders, $input->page, $input->pageItems);

            return $this->createShareListOrdersGeDataOutputDto($listOrders, $orders);
        } catch (DBNotFoundException $e) {
            throw ShareCreateListOrdersNotFoundException::fromMessage('List orders not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ShareListOrdersGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @throws DBNotFoundException
     */
    private function getSharedListOrders(Identifier $listOrdersIdShared): Share
    {
        $sharedListsOrders = $this->shareGetResourcesService->__invoke(
            $this->createShareListOrdersCreateDto($listOrdersIdShared)
        );

        if (empty($sharedListsOrders)) {
            throw new DBNotFoundException();
        }

        return $sharedListsOrders[0];
    }

    /**
     * @throws DBNotFoundException
     */
    private function getListOrders(Share $listOrdersShared, PaginatorPage $page, PaginatorPageItems $pageItems): ListOrders
    {
        $listOrdersData = $this->listOrdersGetDataService->__invoke(
            $this->createListOrdersGetDataDto($listOrdersShared, $page, $pageItems)
        );

        if (empty($listOrdersData)) {
            throw new DBNotFoundException();
        }

        $listOrders = ListOrders::fromPrimitives(
            $listOrdersData[0]['id'],
            $listOrdersData[0]['group_id'],
            $listOrdersData[0]['user_id'],
            $listOrdersData[0]['name'],
            $listOrdersData[0]['description'],
            (new \DateTime())->createFromFormat('Y-m-d H:i:s', $listOrdersData[0]['date_to_buy']),
        );

        return $listOrders;
    }

    /**
     * @return array<int, array{
     *  order: Order,
     *  productShop: ProductShop|null,
     * }>
     *
     * @throws DBNotFoundException
     */
    private function getOrders(ListOrders $listOrders, PaginatorPage $page, PaginatorPageItems $pageItems): array
    {
        $ordersData = $this->orderGetDataService->__invoke(
            $this->createOrderGetDataDto($listOrders, $page, $pageItems)
        );

        if (empty($ordersData)) {
            throw new DBNotFoundException();
        }

        $orders = [];
        foreach ($ordersData as $orderData) {
            $product = Product::fromPrimitives(
                $orderData['product']['id'],
                $orderData['group_id'],
                $orderData['product']['name'],
                $orderData['product']['description'],
                $orderData['product']['image'],
            );
            $product->setCreatedOn((new \DateTime())->createFromFormat('Y-m-d H:i:s', $orderData['product']['created_on']));

            $shop = null;
            if (!empty($orderData['shop'])) {
                $shop = Shop::fromPrimitives(
                    $orderData['shop']['id'],
                    $orderData['group_id'],
                    $orderData['shop']['name'],
                    $orderData['shop']['address'],
                    $orderData['shop']['description'],
                    $orderData['shop']['image'],
                );
                $shop->setCreatedOn((new \DateTime())->createFromFormat('Y-m-d H:i:s', $orderData['shop']['created_on']));
            }

            $productShop = null;
            if (!empty($orderData['productShop'])) {
                $productShop = ProductShop::fromPrimitives(
                    $product,
                    $shop,
                    $orderData['productShop']['price'],
                    $orderData['productShop']['unit'],
                );
            }

            $order = Order::fromPrimitives(
                $orderData['id'],
                $orderData['group_id'],
                $orderData['user_id'],
                $orderData['description'],
                $orderData['amount'],
                $orderData['bought'],
                $listOrders,
                $product,
                $shop
            );
            $order->setCreatedOn((new \DateTime())->createFromFormat('Y-m-d H:i:s', $orderData['created_on']));

            $orders[] = [
                'order' => $order,
                'productShop' => $productShop,
            ];
        }

        return $orders;
    }

    private function createShareListOrdersCreateDto(Identifier $listOrdersId): ShareGetResourcesDto
    {
        return new ShareGetResourcesDto([$listOrdersId]);
    }

    private function createListOrdersGetDataDto(Share $sharedRecourse, PaginatorPage $page, PaginatorPageItems $pageItems): ListOrdersGetDataDto
    {
        return new ListOrdersGetDataDto(
            $sharedRecourse->getGroupId(),
            [$sharedRecourse->getListOrdersId()],
            true,
            null,
            null,
            $page,
            $pageItems
        );
    }

    private function createOrderGetDataDto(ListOrders $listOrders, PaginatorPage $page, PaginatorPageItems $pageItems): OrderGetDataDto
    {
        return new OrderGetDataDto(
            $listOrders->getGroupId(),
            ValueObjectFactory::createIdentifierNullable($listOrders->getId()->getValue()),
            [],
            $page,
            $pageItems,
            true,
            null,
            null,
        );
    }

    /**
     * @param array<int, array{
     *  order: Order,
     *  productShop: ProductShop,
     * }> $orders
     */
    private function createShareListOrdersGeDataOutputDto(ListOrders $listOrders, array $orders): ShareListOrdersGetDataOutputDto
    {
        return new ShareListOrdersGetDataOutputDto($listOrders, $orders);
    }
}
