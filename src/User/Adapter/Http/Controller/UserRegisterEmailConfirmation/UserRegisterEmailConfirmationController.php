<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRegisterEmailConfirmation;

use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationRequestDto;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationInputDto;
use User\Application\UserRegisterEmailConfirmation\UserRegisterEmailConfirmationUseCase;

#[OA\Tag('User')]
#[OA\Patch(
    description: 'Confirms or validate the users email',
    requestBody: new OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'token', description: 'Token send to the users email on sign up', type: 'string', example: 'A large chunk of characters'),
                ]
            )
        )]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'User activated',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User activated'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<username, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'User could not be activated',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<token_error|token_expired|email_verified|token, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserRegisterEmailConfirmationController extends AbstractController
{
    private UserRegisterEmailConfirmationUseCase $userRegisterEmailConfirmationUseCase;

    public function __construct(UserRegisterEmailConfirmationUseCase $userRegisterEmailConfirmationUseCase)
    {
        $this->userRegisterEmailConfirmationUseCase = $userRegisterEmailConfirmationUseCase;
    }

    public function __invoke(UserEmailConfirmationRequestDto $request): JsonResponse
    {
        $user = $this->userRegisterEmailConfirmationUseCase->__invoke(new UserEmailConfirmationInputDto($request->token));
        $response = new ResponseDto(message: 'User activated', data: ['username' => $user->id->getValue()]);

        return $this->json($response, Response::HTTP_OK);
    }
}
