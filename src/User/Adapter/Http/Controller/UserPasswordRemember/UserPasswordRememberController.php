<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordRemember;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordRemember\Dto\UserPasswordRememberRequestDto;
use User\Application\UserPasswordRemember\Dto\UserPasswordRememberInputDto;
use User\Application\UserPasswordRemember\UserPasswordRememberService;

class UserPasswordRememberController extends AbstractController
{
    private UserPasswordRememberService $userPasswordRememberService;

    public function __construct(UserPasswordRememberService $userPasswordRememberService)
    {
        $this->userPasswordRememberService = $userPasswordRememberService;
    }

    public function __invoke(UserPasswordRememberRequestDto $passwordRememberDto): JsonResponse
    {
        $this->userPasswordRememberService->__invoke(
            $this->createUserPasswordRememberInputDto($passwordRememberDto->email)
        );

        return $this->json($this->createResponseDto(), Response::HTTP_OK);
    }

    private function createUserPasswordRememberInputDto(string|null $email): UserPasswordRememberInputDto
    {
        return new UserPasswordRememberInputDto($email);
    }

    private function createResponseDto(): ResponseDto
    {
        return new ResponseDto(message: 'Request acepted', status: RESPONSE_STATUS::OK);
    }
}
