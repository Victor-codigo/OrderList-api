<?php

declare(strict_types=1);

namespace Shop\Adapter\Http\Controller\ShopModify;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Shop\Adapter\Http\Controller\ShopModify\Dto\ShopModifyRequestDto;
use Shop\Application\ShopModify\Dto\ShopModifyInputDto;
use Shop\Application\ShopModify\ShopModifyUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Shop')]
#[OA\Put(
    description: 'Modify a shop',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'shop_id', type: 'string', description: 'Shop\'s id', example: '396e0152-d501-45d9-bf58-7498e11ea6c5'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Shop\'s name', example: 'Shop name'),
                        new OA\Property(property: 'address', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::ADDRESS_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::ADDRESS_MAX_LENGTH, description: 'Shop\'s address', example: 'Shop address'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Shop description', example: 'This is the description of the shop'),
                        new OA\Property(property: 'image_remove', type: 'boolean', description: 'TRUE if the shop image is removed, FALSE no'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Shop image'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The shop has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Shop modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shop could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<shop_id|group_id|name|address|description|shop_not_found|shop_name_repeated|image, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have not grants in this group',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Not permissions in this group'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<permissions, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ShopModifyController extends AbstractController
{
    public function __construct(
        private ShopModifyUseCase $ShopModifyUseCase,
        private Security $security,
    ) {
    }

    public function __invoke(ShopModifyRequestDto $request): JsonResponse
    {
        $userModified = $this->ShopModifyUseCase->__invoke(
            $this->createShopModifyInputDto($request->shopId, $request->groupId, $request->name, $request->address, $request->description, $request->image, $request->imageRemove)
        );

        return $this->createResponse($userModified);
    }

    private function createShopModifyInputDto(?string $shopId, ?string $groupId, ?string $name, ?string $address, ?string $description, ?UploadedFile $image, bool $imageRemove): ShopModifyInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new ShopModifyInputDto(
            $userAdapter->getUser(),
            $shopId,
            $groupId,
            $name,
            $address,
            $description,
            null === $image ? null : new UploadedFileSymfonyAdapter($image),
            $imageRemove
        );
    }

    private function createResponse(ApplicationOutputInterface $shopModified): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Shop modified')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($shopModified->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
