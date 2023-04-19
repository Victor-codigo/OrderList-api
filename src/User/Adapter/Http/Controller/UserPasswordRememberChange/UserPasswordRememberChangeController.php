<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRememberChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordRememberChange\Dto\UserPasswordRememberChangeRequestDto;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto;
use User\Application\UserPasswordRememberChange\UserPasswordRememberChangeUseCase;

#[OA\Tag('User')]
#[OA\Patch(
    description: 'Changes the user\'s password',
    requestBody: new OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'token', description: 'Token forward to the users email', type: 'string', example: 'A chunk of characters'),
                    new OA\Property(property: 'passwordNew', description: 'Current user\'s password', type: 'string', example: 'My current password'),
                    new OA\Property(property: 'passwordNewRepeat', description: 'User\'s new password', type: 'string', example: 'My new password'),
                ]
            )
        )]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'Password changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Password changed'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'Password changed',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<token_wrong|token_expired|passwordNew|passwordNewRepeat|password_change|password_repeat, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserPasswordRememberChangeController extends AbstractController
{
    private UserPasswordRememberChangeUseCase $userPasswordRememberChangeUseCase;

    public function __construct(UserPasswordRememberChangeUseCase $userPasswordRememberChangeUseCase)
    {
        $this->userPasswordRememberChangeUseCase = $userPasswordRememberChangeUseCase;
    }

    public function __invoke(UserPasswordRememberChangeRequestDto $request): JsonResponse
    {
        $this->userPasswordRememberChangeUseCase->__invoke(
            $this->createUserPasswordRememberChangeInputDto($request->token, $request->passwordNew, $request->passwordNewRepeat)
        );

        return $this->json(
            new ResponseDto(message: 'Password changed', status: RESPONSE_STATUS::OK),
            Response::HTTP_OK
        );
    }

    private function createUserPasswordRememberChangeInputDto(string|null $token, string|null $passwordNew, string|null $passwordNewRepeat): UserPasswordRememberChangeInputDto
    {
        return new UserPasswordRememberChangeInputDto(
            $token,
            $passwordNew,
            $passwordNewRepeat
        );
    }
}
