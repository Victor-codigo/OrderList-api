<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderModify;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrderModify\Dto\OrderModifyRequestDto;
use Order\Application\OrderModify\Dto\OrderModifyInputDto;
use Order\Application\OrderModify\OrderModifyUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Put(
    description: 'Modifies an order',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'list_orders_ud', type: 'string', description: 'List orders id', example: '0630d868-e1d5-4c17-84c8-002eb28f5bdb'),
                        new OA\Property(property: 'order_id', type: 'string', description: 'Order id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'product_id', type: 'string', description: 'Product id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'shop_id', type: 'string', description: 'Shop id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'description', type: 'string', description: 'Order description'),
                        new OA\Property(property: 'amount', type: 'float', description: 'Order amount of product'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The order has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The order could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<order_id|group_id|list_orders_id|product_id|shop_id|description|amount|list_orders_not_found|order_not_found|product_not_found|shop_not_found|group_error, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrderModifyController extends AbstractController
{
    public function __construct(
        private OrderModifyUseCase $OrderModifyUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrderModifyRequestDto $request): JsonResponse
    {
        $orderModify = $this->OrderModifyUseCase->__invoke(
            $this->createOrderModifyInputDto(
                $request->groupId,
                $request->listOrdersId,
                $request->orderId,
                $request->productId,
                $request->shopId,
                $request->description,
                $request->amount,
            )
        );

        return $this->createResponse($orderModify);
    }

    private function createOrderModifyInputDto(?string $groupId, ?string $listOrdersId, ?string $orderId, ?string $productId, ?string $shopId, ?string $description, ?float $amount): OrderModifyInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new OrderModifyInputDto(
            $userAdapter->getUser(),
            $groupId,
            $listOrdersId,
            $orderId,
            $productId,
            $shopId,
            $description,
            $amount,
        );
    }

    private function createResponse(ApplicationOutputInterface $orderModify): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Order modified')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($orderModify->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
