<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemoveOrder;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderRequestDto;
use ListOrders\Application\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderInputDto;
use ListOrders\Application\ListOrdersRemoveOrder\ListOrdersRemoveOrderUseCase;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Delete(
    description: 'Removes orders from list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id of the order', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'lists_orders_id', type: 'array', description: 'list of orders id', items: new Items(default: '9d1a5942-850f-41f9-a32a-38927978ce5c')),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Orders have been removed from the list of orders',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Orders removed from list of orders'),
                        new OA\Property(property: 'data', type: 'array', items: new Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Orders could not been removed from the list of orders',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new Items(default: '<list_orders_id|group_id|orders_id_empty|orders_id|orders_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersRemoveOrderController extends AbstractController
{
    public function __construct(
        private ListOrdersRemoveOrderUseCase $ListOrdersRemoveOrderUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersRemoveOrderRequestDto $request): JsonResponse
    {
        $listOrdersRemoved = $this->ListOrdersRemoveOrderUseCase->__invoke(
            $this->createListOrdersRemoveOrderInputDto($request->groupId, $request->listsOrdersId)
        );

        return $this->createResponse($listOrdersRemoved);
    }

    /**
     * @param string[]|null $listOrdersId
     */
    private function createListOrdersRemoveOrderInputDto(string|null $groupId, array|null $listOrdersId): ListOrdersRemoveOrderInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersRemoveOrderInputDto($userSharedAdapter->getUser(), $groupId, $listOrdersId);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Orders removed from list of orders')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
