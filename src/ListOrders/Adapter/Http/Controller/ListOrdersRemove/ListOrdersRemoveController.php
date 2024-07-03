<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersRemove;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersRemove\Dto\ListOrdersRemoveRequestDto;
use ListOrders\Application\ListOrdersRemove\Dto\ListOrdersRemoveInputDto;
use ListOrders\Application\ListOrdersRemove\ListOrdersRemoveUseCase;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Delete(
    description: 'Removes a list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'lists_orders_id', type: 'array', description: 'lists of orders id', items: new Items(default: '[0290bf7e-2e68-4698-ba2e-d2394c239572]')),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id of the order', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The list of orders has been removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List of orders removed'),
                        new OA\Property(property: 'data', type: 'array', items: new Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The list of orders could not be removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new Items(default: '<list_orders_id|group_id|permissions|lists_orders_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersRemoveController extends AbstractController
{
    public function __construct(
        private ListOrdersRemoveUseCase $listOrdersRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersRemoveRequestDto $request): JsonResponse
    {
        $listOrdersRemovedId = $this->listOrdersRemoveUseCase->__invoke(
            $this->createListOrdersRemoveInputDto($request->groupId, $request->listsOrdersId)
        );

        return $this->createResponse($listOrdersRemovedId);
    }

    private function createListOrdersRemoveInputDto(?string $groupId, ?array $listsOrdersId): ListOrdersRemoveInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersRemoveInputDto($userSharedAdapter->getUser(), $groupId, $listsOrdersId);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
