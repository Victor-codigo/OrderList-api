<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersCreateFrom;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersCreateFrom\Dto\ListOrdersCreateFromRequestDto;
use ListOrders\Application\ListOrdersCreateFrom\Dto\ListOrdersCreateFromInputDto;
use ListOrders\Application\ListOrdersCreateFrom\ListOrdersCreateFromUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Post(
    description: 'Creates a list of orders from another',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'list_orders_id_create_from', type: 'string', description: 'List orders id from witch create a copy', example: 'c11b0638-c33d-41bc-8720-bebc3eed46db'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id to add the list of orders', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'name', type: 'string', description: 'Name of the list of orders', example: 'List name'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The list of orders has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List order created from other list of orders'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The List of orders could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_id_create_from|group_id|name|list_orders_create_from_not_found|name_exists|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersCreateFromController extends AbstractController
{
    public function __construct(
        private ListOrdersCreateFromUseCase $listOrdersCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersCreateFromRequestDto $request): JsonResponse
    {
        $listOrdersIdCreated = $this->listOrdersCreateUseCase->__invoke(
            $this->createListOrdersCreateInputDto($request->listOrdersIdCreateFrom, $request->groupId, $request->name)
        );

        return $this->createResponse($listOrdersIdCreated);
    }

    private function createListOrdersCreateInputDto(?string $listOrdersIdCreateFrom, ?string $groupId, ?string $name): ListOrdersCreateFromInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersCreateFromInputDto($userSharedAdapter->getUser(), $listOrdersIdCreateFrom, $groupId, $name);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersIdCreated): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List order created from other list of orders')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersIdCreated->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
