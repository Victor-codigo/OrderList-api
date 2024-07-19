<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductGetFirstLetter;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductGetFirstLetter\Dto\ProductGetFirstLetterRequestDto;
use Product\Application\ProductGetFirstLetter\Dto\ProductGetFirstLetterInputDto;
use Product\Application\ProductGetFirstLetter\ProductGetFirstLetterUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Get(
    description: 'Get a list with the first letter of all products in a group',
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
            description: 'Returns the first letter of products saved in data base',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Products first letter'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),

        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Products not found',
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
class ProductGetFirstLetterController extends AbstractController
{
    public function __construct(
        private ProductGetFirstLetterUseCase $productGetFirstLetterUseCase
    ) {
    }

    public function __invoke(ProductGetFirstLetterRequestDto $request): JsonResponse
    {
        $productsFirstLetter = $this->productGetFirstLetterUseCase->__invoke(
            $this->createProductGetFirstLetterInputDto($request)
        );

        return $this->createResponse($productsFirstLetter);
    }

    private function createProductGetFirstLetterInputDto(ProductGetFirstLetterRequestDto $request): ProductGetFirstLetterInputDto
    {
        return new ProductGetFirstLetterInputDto($request->groupId);
    }

    private function createResponse(ApplicationOutputInterface $productsFirstLetter): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Products first letter')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productsFirstLetter->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
