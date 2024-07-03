<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersModify;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersModify\Dto\ListOrdersModifyRequestDto;
use ListOrders\Application\ListOrdersModify\Dto\ListOrdersModifyInputDto;
use ListOrders\Application\ListOrdersModify\ListOrdersModifyUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Put(
    description: 'Modifies a list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'list_orders_id', type: 'string', description: 'Lit orders id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'name', type: 'string', description: 'Product id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'description', type: 'string', description: 'List orders description'),
                        new OA\Property(property: 'date_to_buy', type: 'string', description: 'Date limit to buy the list of orders'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The list of orders has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List of orders modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The list of orders could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_id|group_id|name|description|permissions|list_orders_not_found|list_orders_name_exists, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersModifyController extends AbstractController
{
    public function __construct(
        private ListOrdersModifyUseCase $ListOrdersModifyUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersModifyRequestDto $request): JsonResponse
    {
        $listOrdersModifiedId = $this->ListOrdersModifyUseCase->__invoke(
            $this->createListOrdersModifyInputDto($request->listOrdersId, $request->groupId, $request->name, $request->description, $request->dateToBuy)
        );

        return $this->createResponse($listOrdersModifiedId);
    }

    private function createListOrdersModifyInputDto(?string $listOrdersId, ?string $groupId, ?string $name, ?string $description, ?string $dateToBuy): ListOrdersModifyInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersModifyInputDto($userSharedAdapter->getUser(), $listOrdersId, $groupId, $name, $description, $dateToBuy);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersModified): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders modified')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersModified->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
