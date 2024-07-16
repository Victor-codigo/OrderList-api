<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopGetFirstLetter;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopGetFirstLetter\Dto\ShopGetFirstLetterRequestDto;
use Shop\Application\ShopGetFirstLetter\Dto\ShopGetFirstLetterInputDto;
use Shop\Application\ShopGetFirstLetter\ShopGetFirstLetterUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Get(
    description: 'Get for a group, shop\'s first letter saved in data base',
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
            description: 'Returns the first letter of shops saved in data base',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shops first letter'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Some error message',
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
            response: Response::HTTP_NO_CONTENT,
            description: 'Shops not found',
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
class ShopGetFirstLetterController extends AbstractController
{
    public function __construct(
        private ShopGetFirstLetterUseCase $shopGetFirstLetterUseCase
    ) {
    }

    public function __invoke(ShopGetFirstLetterRequestDto $request): JsonResponse
    {
        $shopsFirstLetter = $this->shopGetFirstLetterUseCase->__invoke(
            $this->createShopGetFirstLetterInputDto($request)
        );

        return $this->createResponse($shopsFirstLetter);
    }

    private function createShopGetFirstLetterInputDto(ShopGetFirstLetterRequestDto $request): ShopGetFirstLetterInputDto
    {
        return new ShopGetFirstLetterInputDto($request->groupId);
    }

    private function createResponse(ApplicationOutputInterface $shopsFirstLetter): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Shops first letter')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shopsFirstLetter->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
