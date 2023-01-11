<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRemove;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Http\Controller\UserRemove\Dto\UserRemoveRequestDto;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Application\UserRemove\Dto\UserRemoveInputDto;
use User\Application\UserRemove\UserRemoveUseCase;

#[OA\Tag('User')]
#[OA\Delete(
    description: 'Deletes an user from database',
    requestBody: new OA\RequestBody(
        required: false,
        description: 'Body is not required',
        content: new OA\JsonContent()
    ),
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'User\'s id',
            schema: new OA\Schema(type: 'string'),
            example: '2606508b-4516-45d6-93a6-c7cb416b7f3f'
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User Removed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User Removed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'An errors has been occurred with the request',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'Error'),
                        new OA\Property(property: 'message', type: 'string', example: 'An error messsage'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<id|permissions, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'User not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'Error'),
                        new OA\Property(property: 'message', type: 'string', example: 'User not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<user_not_found, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserRemoveController extends AbstractController
{
    public function __construct(
        private UserRemoveUseCase $userRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(UserRemoveRequestDto $request): JsonResponse
    {
        $this->userRemoveUseCase->__invoke(
            $this->createUseRemoveInputDto($request->userId)
        );

        return $this->createResponse();
    }

    private function createUseRemoveInputDto(string|null $userId): UserRemoveInputDto
    {
        /** @var UserSymfonyAdapter $user */
        $user = $this->security->getUser();

        return new UserRemoveInputDto($user->getUser(), $userId);
    }

    private function createResponse(): JsonResponse
    {
        $response = new ResponseDto([], [], 'User Removed', RESPONSE_STATUS::OK);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
