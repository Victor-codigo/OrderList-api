<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopCreate;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopCreate\Dto\ShopCreateRequestDto;
use Shop\Application\ShopCreate\Dto\ShopCreateInputDto;
use Shop\Application\ShopCreate\Dto\ShopCreateOutputDto;
use Shop\Application\ShopCreate\ShopCreateUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Post(
    description: 'Creates a Shop',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id to add the shop', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Shop\'s name', example: 'Shop name'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Shop description', example: 'This is the description of the shop'),
                        new OA\Property(property: 'address', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::ADDRESS_MAX_LENGTH, description: 'Shop address', example: 'Calle Vitoria, 10 3b'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Shop\'s image'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The shop has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shop created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shop could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<description|address|group_id|permissions|name|shop_name_repeated|image, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ShopCreateController extends AbstractController
{
    public function __construct(
        private ShopCreateUseCase $shopCreateUseCase,
        private Security $security,
    ) {
    }

    public function __invoke(ShopCreateRequestDto $request): JsonResponse
    {
        $shop = $this->shopCreateUseCase->__invoke(
            $this->createShopCreateInputDto($request->groupId, $request->name, $request->address, $request->description, $request->image)
        );

        return $this->createResponse($shop);
    }

    private function createShopCreateInputDto(?string $groupId, ?string $name, ?string $address, ?string $description, ?UploadedFile $image): ShopCreateInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapterSymfony */
        $userAdapterSymfony = $this->security->getUser();

        return new ShopCreateInputDto(
            $userAdapterSymfony->getUser(),
            $groupId,
            $name,
            $address,
            $description,
            null === $image ? null : new UploadedFileSymfonyAdapter($image)
        );
    }

    private function createResponse(ShopCreateOutputDto $shop): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Shop created')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shop->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
