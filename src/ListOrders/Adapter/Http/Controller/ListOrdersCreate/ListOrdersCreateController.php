<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersCreate;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersCreate\Dto\ListOrdersCreateRequestDto;
use ListOrders\Application\ListOrdersCreate\Dto\ListOrdersCreateInputDto;
use ListOrders\Application\ListOrdersCreate\ListOrdersCreateUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Post(
    description: 'Creates a list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id to add the list of orders', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'name', type: 'string', description: 'Name of the list of orders', example: 'List name'),
                        new OA\Property(property: 'description', type: 'string', description: 'Description of the list of orders', example: 'List of orders description'),
                        new OA\Property(property: 'date_to_buy', type: 'string', description: 'Date to buy the list', example: '2023-6-3 12:30:00'),
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
                        new OA\Property(property: 'message', type: 'string', example: 'List of orders created'),
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<group_id|name|name_exists|permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersCreateController extends AbstractController
{
    public function __construct(
        private ListOrdersCreateUseCase $listOrdersCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ListOrdersCreateRequestDto $request): JsonResponse
    {
        $listOrdersIdCreated = $this->listOrdersCreateUseCase->__invoke(
            $this->createListOrdersCreateInputDto($request->groupId, $request->name, $request->description, $request->dateToBuy)
        );

        return $this->createResponse($listOrdersIdCreated);
    }

    private function createListOrdersCreateInputDto(string|null $groupId, string|null $name, string|null $description, string|null $dateToBuy): ListOrdersCreateInputDto
    {
        /** @var UserSharedSymfonyAdapter $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ListOrdersCreateInputDto($userSharedAdapter->getUser(), $groupId, $name, $description, $dateToBuy);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersIdCreated): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List order created')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersIdCreated->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
