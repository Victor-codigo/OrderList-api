<?php

declare(strict_types=1);

namespace Share\Adapter\Http\Controller\ShareListOrdersCreate;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Share\Adapter\Http\Controller\ShareListOrdersCreate\Dto\ShareListOrdersCreateRequestDto;
use Share\Application\ShareListOrdersCreate\Dto\ShareListOrdersCreateInputDto;
use Share\Application\ShareListOrdersCreate\ShareListOrdersCreateUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Share')]
#[OA\Post(
    description: 'Creates a shared list of orders',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'list_orders_id', type: 'string', description: 'List orders id to add to the shared resource', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The shared list of orders has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'List orders shared'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The shared list of orders could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<list_orders_id|list_orders_not_found, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_UNAUTHORIZED,
            description: 'You have no permissions',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'code', type: 'int', example: 401),
                        new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
                    ]
                )
            )
        ),
    ]
)]
class ShareListOrdersCreateController extends AbstractController
{
    public function __construct(
        private ShareListOrdersCreateUseCase $sharedListOrdersCreateUseCase,
        private Security $security,
    ) {
    }

    public function __invoke(ShareListOrdersCreateRequestDto $request): JsonResponse
    {
        $listOrdersShared = $this->sharedListOrdersCreateUseCase->__invoke(
            $this->createListOrdersSharedCreateInputDto($request->listOrdersId)
        );

        return $this->createResponse($listOrdersShared);
    }

    private function createListOrdersSharedCreateInputDto(?string $listOrdersId): ShareListOrdersCreateInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapterSymfony */
        $userAdapterSymfony = $this->security->getUser();

        return new ShareListOrdersCreateInputDto(
            $userAdapterSymfony->getUser(),
            $listOrdersId
        );
    }

    private function createResponse(ApplicationOutputInterface $sharedList): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('List orders shared')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($sharedList->toArray());

        return new JsonResponse($responseDto, Response::HTTP_CREATED);
    }
}
