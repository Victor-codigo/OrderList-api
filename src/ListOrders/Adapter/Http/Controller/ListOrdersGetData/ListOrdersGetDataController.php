<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetData;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersGetData\Dto\ListOrdersGetDataRequestDto;
use ListOrders\Application\ListOrdersGetData\Dto\ListOrdersGetDataInputDto;
use ListOrders\Application\ListOrdersGetData\ListOrdersGetDataUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Get(
    description: 'Get list of order\'s data',
    parameters: [
        new OA\Parameter(
            name: 'group_id',
            in: 'query',
            required: true,
            description: 'Group id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'list_orders_ids',
            in: 'query',
            required: false,
            description: 'List of orders id separated by comas',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,428e3645-91fb-4239-8b52-b49a056eb2e7',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'list_orders_name_starts_with',
            in: 'query',
            required: false,
            description: 'List of orders name that starts with',
            example: 'list orders name',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'List of order\'s data',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product\'s data'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string'),
                                new OA\Property(property: 'user_id', type: 'string'),
                                new OA\Property(property: 'group_id', type: 'string'),
                                new OA\Property(property: 'name', type: 'string'),
                                new OA\Property(property: 'description', type: 'string'),
                                new OA\Property(property: 'date_to_buy', type: 'string'),
                                new OA\Property(property: 'created_on', type: 'string'),
                            ])),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The list of orders could not be found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_ids_and_name_starts_with_empty|list_orders_name_starts_with|group_id|list_orders_ids|list_orders_not_found|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersGetDataController extends AbstractController
{
    public function __construct(
        private ListOrdersGetDataUseCase $ListOrdersGetDataUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersGetDataRequestDto $request): JsonResponse
    {
        $listOrderData = $this->ListOrdersGetDataUseCase->__invoke(
            $this->createListOrdersGetDataInputDto($request->listOrdersIds, $request->groupId, $request->listOrdersNameStartsWith)
        );

        return $this->createResponse($listOrderData);
    }

    private function createListOrdersGetDataInputDto(array|null $listOrdersIds, string|null $groupId, string|null $listOrdersNameStartsWith): ListOrdersGetDataInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersGetDataInputDto($userSharedAdapter->getUser(), $listOrdersIds, $groupId, $listOrdersNameStartsWith);
    }

    private function createResponse(ApplicationOutputInterface $listOrderData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders data')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrderData->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
