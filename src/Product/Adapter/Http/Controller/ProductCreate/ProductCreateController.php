<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductCreate;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Product\Adapter\Http\Controller\ProductCreate\Dto\ProductCreateRequestDto;
use Product\Application\ProductCreate\Dto\ProductCreateInputDto;
use Product\Application\ProductCreate\Dto\ProductCreateOutputDto;
use Product\Application\ProductCreate\ProductCreateUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Product')]
#[OA\Post(
    description: 'Creates a product',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id to add the product', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'Product\'s name', example: 'Product name'),
                        new OA\Property(property: 'description', type: 'string', maxLength: VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH, description: 'Product description', example: 'This is the description of the product'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Product\'s image'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The product has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Product created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The product could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<description|group_id|group_error|name|product_name_repeated|image, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class ProductCreateController extends AbstractController
{
    public function __construct(
        private ProductCreateUseCase $productCreateUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductCreateRequestDto $request): JsonResponse
    {
        $product = $this->productCreateUseCase->__invoke(
            $this->createProductCreateInputDto($request->groupId, $request->name, $request->description, $request->image)
        );

        return $this->createResponse($product);
    }

    private function createProductCreateInputDto(string|null $groupId, string|null $name, string|null $description, UploadedFile|null $image): ProductCreateInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapterSymfony */
        $userAdapterSymfony = $this->security->getUser();

        return new ProductCreateInputDto(
            $userAdapterSymfony->getUser(),
            $groupId,
            $name,
            $description,
            null === $image ? null : new UploadedFileSymfonyAdapter($image)
        );
    }

    private function createResponse(ProductCreateOutputDto $product): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Product created')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($product->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
