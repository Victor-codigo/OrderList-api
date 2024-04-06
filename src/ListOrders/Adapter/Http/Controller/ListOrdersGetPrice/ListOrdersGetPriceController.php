<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetPrice;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersGetPrice\Dto\ListOrdersGetPriceRequestDto;
use ListOrders\Application\ListOrdersGetPrice\Dto\ListOrdersGetPriceInputDto;
use ListOrders\Application\ListOrdersGetPrice\ListOrdersGetPriceUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Get(
    description: 'Get total and bought price of a list of orders',
    parameters: [
        new OA\Parameter(
            name: 'list_orders_id',
            in: 'query',
            required: true,
            description: 'List of orders id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'group_id',
            in: 'query',
            required: true,
            description: 'Group id',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f',
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
                                new OA\Property(property: 'total', type: 'float'),
                                new OA\Property(property: 'bought', type: 'float'),
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_id|list_orders_not_found|group_id|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersGetPriceController extends AbstractController
{
    public function __construct(
        private ListOrdersGetPriceUseCase $listOrdersGetPriceUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersGetPriceRequestDto $request): JsonResponse
    {
        $listOrdersModifiedId = $this->listOrdersGetPriceUseCase->__invoke(
            $this->createListOrdersGetPriceInputDto($request->listOrdersId, $request->groupId)
        );

        return $this->createResponse($listOrdersModifiedId);
    }

    private function createListOrdersGetPriceInputDto(?string $listOrdersId, ?string $groupId): ListOrdersGetPriceInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersGetPriceInputDto($userSharedAdapter->getUser(), $listOrdersId, $groupId);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersModified): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders price')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersModified->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
