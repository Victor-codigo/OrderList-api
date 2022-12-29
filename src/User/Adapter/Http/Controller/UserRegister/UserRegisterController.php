<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRegister;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserRegister\Dto\UserRegisterRequestDto;
use User\Application\UserRegister\Dto\UserRegisterInputDto;
use User\Application\UserRegister\UserRegisterService;
use User\Domain\Model\USER_ROLES;

#[OA\Tag('User')]
#[OA\Post(
    description: 'Registers an user',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'name', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH, description: 'User\'s name', example: 'Mary'),
                        new OA\Property(property: 'email', type: 'string', description: 'User\'s email', example: 'Mary@hotmail.com'),
                        new OA\Property(property: 'password', type: 'string', minLength: VALUE_OBJECTS_CONSTRAINTS::PASSWORD_MIN_LENGTH, maxLength: VALUE_OBJECTS_CONSTRAINTS::PASSWORD_MAX_LENGTH, description: 'User\'s password', example: 'My password'),
                        new OA\Property(property: 'email_confirmation_url', type: 'string', description: 'URL of the endpoint where the email is confirmed', example: 'http://somain.com/users/email/confirm'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_CREATED,
            description: 'The user has been created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'User created'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, string>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The user could not be created',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<email_exists|name|email|password|email_confirmation_url, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class UserRegisterController extends AbstractController
{
    private UserRegisterService $UserRegisterService;

    public function __construct(UserRegisterService $UserRegisterService)
    {
        $this->UserRegisterService = $UserRegisterService;
    }

    public function __invoke(UserRegisterRequestDto $requestDto): JsonResponse
    {
        $UserRegisterOutputDto = $this->UserRegisterService->__invoke(
            $this->createUserRegisterInputDto($requestDto)
        );

        $response = (new ResponseDto())
            ->setMessage('User created')
            ->setData(['id' => $UserRegisterOutputDto->id->getValue()]);

        return $this->json($response->toArray(), Response::HTTP_CREATED);
    }

    private function createUserRegisterInputDto(UserRegisterRequestDto $requestDto): UserRegisterInputDto
    {
        return UserRegisterInputDto::create(
            $requestDto->email,
            $requestDto->password,
            $requestDto->name,
            [new Rol(USER_ROLES::NOT_ACTIVE)],
            $requestDto->userRegisterEmailConfirmationUrl
        );
    }
}
