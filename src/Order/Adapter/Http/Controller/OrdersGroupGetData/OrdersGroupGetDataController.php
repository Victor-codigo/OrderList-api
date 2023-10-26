<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrdersGroupGetData;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Order\Adapter\Http\Controller\OrdersGroupGetData\Dto\OrdersGroupGetDataRequestDto;
use Order\Application\OrdersGroupGetData\Dto\OrdersGroupGetDataInputDto;
use Order\Application\OrdersGroupGetData\OrdersGroupGetDataUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrdersGroupGetDataController extends AbstractController
{
    public function __construct(
        private OrdersGroupGetDataUseCase $OrdersGroupGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrdersGroupGetDataRequestDto $request): JsonResponse
    {
        $ordersGroupData = $this->OrdersGroupGetDataUseCase->__invoke(
            $this->createOrdersGroupGetDataInputDto($request->groupId, $request->page, $request->pageItems)
        );

        return $this->createResponse($ordersGroupData);
    }

    private function createOrdersGroupGetDataInputDto(string|null $groupId, int|null $page, int|null $pageItems): OrdersGroupGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new OrdersGroupGetDataInputDto($userAdapter->getUser(), $groupId, $page, $pageItems);
    }

    private function createResponse(ApplicationOutputInterface $ordersGroupData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders of the group data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($ordersGroupData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
