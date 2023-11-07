<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductModify;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductModify\Dto\ProductModifyRequestDto;
use Product\Application\ProductModify\Dto\ProductModifyInputDto;
use Product\Application\ProductModify\ProductModifyUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Put(
    description: 'Modify a product',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'product_id', type: 'string', description: 'Product\'s id', example: '44640237-4ae5-4a10-b30b-d8aee64492b0'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group\'s id', example: 'fdb242b4-bac8-4463-88d0-0941bb0beee0'),
                        new OA\Property(property: 'shop_id', type: 'string', description: 'Shop\'s id', example: '396e0152-d501-45d9-bf58-7498e11ea6c5'),
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Product\'s name', example: 'Product name'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Product description', example: 'This is the description of the product'),
                        new OA\Property(property: 'price', type: 'float', description: 'Price of the product in the shop'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Product image'),
                        new OA\Property(property: 'image_remove', type: 'boolean', description: 'TRUE if the product image is removed, FALSE no'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The product has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product modified'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The product could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<product_id|group_id|shop_not_found|name|description|product_not_found|image, string|array>')),
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
class ProductModifyController extends AbstractController
{
    public function __construct(
        private ProductModifyUseCase $productModifyUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductModifyRequestDto $request): JsonResponse
    {
        $productModified = $this->productModifyUseCase->__invoke(
            $this->createProductModifyInputDto(
                $request->productId,
                $request->groupId,
                $request->shopId,
                $request->name,
                $request->description,
                $request->price,
                $request->image,
                $request->imageRemove
            )
        );

        return $this->createResponse($productModified);
    }

    private function createProductModifyInputDto(string|null $productId, string|null $groupId, string|null $shopId, string|null $name, string|null $description, float|null $price, UploadedFile|null $image, bool $imageRemove): ProductModifyInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new ProductModifyInputDto(
            $userAdapter->getUser(),
            $productId,
            $groupId,
            $shopId,
            $name,
            $description,
            $price,
            null === $image ? null : new UploadedFileSymfonyAdapter($image),
            $imageRemove
        );
    }

    private function createResponse(ApplicationOutputInterface $productModified): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Product modified')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productModified->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
