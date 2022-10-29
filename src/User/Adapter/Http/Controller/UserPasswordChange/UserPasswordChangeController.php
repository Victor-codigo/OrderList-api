<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserPasswordChange;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserPasswordChange\Dto\UserPasswordChangeRequestDto;
use User\Application\UserPasswordChange\Dto\UserPasswordChangeInputDto;
use User\Application\UserPasswordChange\UserPasswordChangeService;

class UserPasswordChangeController extends AbstractController
{
    private UserPasswordChangeService $userPasswordChangeService;

    public function __construct(UserPasswordChangeService $userPasswordChangeService)
    {
        $this->userPasswordChangeService = $userPasswordChangeService;
    }

    public function __invoke(UserPasswordChangeRequestDto $passwordChangeRequestDto): JsonResponse
    {
        $this->userPasswordChangeService->__invoke(
            $this->createUserPasswordChangeInputDto($passwordChangeRequestDto)
        );

        return $this->json(
            new ResponseDto(message: 'Password changed', status: RESPONSE_STATUS::OK),
            Response::HTTP_OK
        );
    }

    private function createUserPasswordChangeInputDto(UserPasswordChangeRequestDto $passwordChangeRequestDto): UserPasswordChangeInputDto
    {
        return new UserPasswordChangeInputDto(
            $passwordChangeRequestDto->id,
            $passwordChangeRequestDto->passwordOld,
            $passwordChangeRequestDto->passwordNew,
            $passwordChangeRequestDto->passwordNewRepeat
        );
    }
}
