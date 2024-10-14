<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserGetByName;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserGetByName\Dto\UserGetByNameRequestDto;
use User\Adapter\Security\User\UserSymfonyAdapter;
use User\Application\UserGetByName\Dto\UserGetByNameInputDto;
use User\Application\UserGetByName\UserGetByNameUseCase;

#[OA\Tag('User')]
#[OA\Get(
    description: 'Get a list with users data, by user name',
    parameters: [
        new OA\Parameter(
            name: 'users_name',
            in: 'path',
            required: true,
            description: 'a list of users names separated by a coma',
            example: 'Maria,Juan,Pedro',
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
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string', example: '1fcab788-0def-4e56-b441-935361678da9'),
                                new OA\Property(property: 'email', type: 'string', example: 'user@email.com'),
                                new OA\Property(property: 'name', type: 'string', example: 'UserName'),
                                new OA\Property(property: 'roles', type: 'string', example: 'ROLE_USER'),
                                new OA\Property(property: 'created_on', type: 'string', example: '22023-2-23 12:00:00'),
                                new OA\Property(property: 'image', type: 'string', example: 'User\'s image'),
                            ])),
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
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<alphanumeric_with_whitespace, string|array>')),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_NO_CONTENT,
            description: 'Users not found',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema()
            )
        ),
    ]
)]
class UserGetByNameController extends AbstractController
{
    public function __construct(
        private UserGetByNameUseCase $UserGetByNameUseCase,
        private Security $security,
    ) {
    }

    public function __invoke(UserGetByNameRequestDto $request): JsonResponse
    {
        $userFound = $this->UserGetByNameUseCase->__invoke(
            $this->createUserGetByNameInputDto($request->usersName)
        );

        return $this->createResponse($userFound->userData);
    }

    /**
     * @param string[]|null $userName
     */
    private function createUserGetByNameInputDto(?array $userName): UserGetByNameInputDto
    {
        /** @var UserSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new UserGetByNameInputDto($userAdapter->getUser(), $userName);
    }

    /**
     * @param array<int, array<string, mixed>> $userData
     */
    private function createResponse(array $userData): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('User found')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($userData);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
