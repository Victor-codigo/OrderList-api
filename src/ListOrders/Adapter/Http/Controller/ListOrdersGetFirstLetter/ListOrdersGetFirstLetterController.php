<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Http\Controller\ListOrdersGetFirstLetter;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterRequestDto;
use ListOrders\Application\ListOrdersGetFirstLetter\Dto\ListOrdersGetFirstLetterInputDto;
use ListOrders\Application\ListOrdersGetFirstLetter\ListOrdersGetFirstLetterUseCase;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('ListOrders')]
#[OA\Get(
    description: 'Get a list with the first letter of all list of orders in a group',
    parameters: [
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
            description: 'Returns the first letter of list orders saved in data base',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List orders first letter'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),

        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'List orders not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions|group_id, string|array>')),
                    ]
                )
            )
        ),

        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'An error occurred',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions|group_id, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ListOrdersGetFirstLetterController extends AbstractController
{
    public function __construct(
        private ListOrdersGetFirstLetterUseCase $listOrdersGetFirstLetterUseCase
    ) {
    }

    public function __invoke(ListOrdersGetFirstLetterRequestDto $request): JsonResponse
    {
        $listOrdersFirstLetter = $this->listOrdersGetFirstLetterUseCase->__invoke(
            $this->createListOrdersGetFirstLetterInputDto($request)
        );

        return $this->createResponse($listOrdersFirstLetter);
    }

    private function createListOrdersGetFirstLetterInputDto(ListOrdersGetFirstLetterRequestDto $request): ListOrdersGetFirstLetterInputDto
    {
        return new ListOrdersGetFirstLetterInputDto($request->groupId);
    }

    private function createResponse(ApplicationOutputInterface $listOrdersFirstLetter): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders first letter')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($listOrdersFirstLetter->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
