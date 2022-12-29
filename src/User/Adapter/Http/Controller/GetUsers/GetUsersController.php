<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\GetUsers;

use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\GetUsers\Dto\GetUsersRequestDto;
use User\Application\GetUsers\Dto\GetUsersInputDto;
use User\Application\GetUsers\GetUsersService;

#[OA\Tag('User')]
#[OA\Get(
    description: 'Get users information',
    parameters: [
        new OA\Parameter(
            name: 'users_id',
            in: 'path',
            required: true,
            description: 'a list of users id separated by a coma',
            example: '5483539d-52f7-4aa9-a91c-1aae11c3d17f,1fcab788-0def-4e56-b441-935361678da9',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Users found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Users found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<int, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_METHOD_NOT_ALLOWED,
            description: 'Not users provided',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Users not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Error getting users',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<uuid_too_long, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NOT_FOUND,
            description: 'Users not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Users not found'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
    ]
)]
class GetUsersController extends AbstractController
{
    private GetUsersService $getUsersService;

    public function __construct(GetUsersService $getUsersService)
    {
        $this->getUsersService = $getUsersService;
    }

    public function __invoke(GetUsersRequestDto $request): JsonResponse
    {
        $response = $this->getUsersService->__invoke(
            $this->createGetUsersInputDto($request->usersId)
        );

        return $this->createResponse($response->users);
    }

    private function createGetUsersInputDto(array|null $usersId): GetUsersInputDto
    {
        return new GetUsersInputDto($usersId);
    }

    private function createResponse(array $users): JsonResponse
    {
        $response = new ResponseDto(message: 'Users found', data: $users);

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
