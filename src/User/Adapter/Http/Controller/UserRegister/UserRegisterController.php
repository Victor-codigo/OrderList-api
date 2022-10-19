<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserRegister;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserRegister\Dto\UserRegisterRequestDto;
use User\Application\UserRegister\Dto\UserRegisterInputDto;
use User\Application\UserRegister\UserRegisterService;
use User\Domain\Model\USER_ROLES;

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
            $requestDto->registrationKey
        );
    }
}
