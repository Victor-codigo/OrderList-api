<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRememberChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordRememberChange\Dto\UserPasswordRememberChangeRequestDto;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto;
use User\Application\UserPasswordRememberChange\UserPasswordRememberChangeService;

class UserPasswordRememberChangeController extends AbstractController
{
    private UserPasswordRememberChangeService $userPasswordRememberChangeService;

    public function __construct(UserPasswordRememberChangeService $userPasswordRememberChangeService)
    {
        $this->userPasswordRememberChangeService = $userPasswordRememberChangeService;
    }

    public function __invoke(UserPasswordRememberChangeRequestDto $request): JsonResponse
    {
        $this->userPasswordRememberChangeService->__invoke(
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
