<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserEmailConfirmation;

use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use User\Adapter\Http\Controller\UserEmailConfirmation\Dto\UserEmailConfirmationRequestDto;
use User\Application\UserEmailComfirmation\Dto\UserEmailConfirmationInputDto;
use User\Application\UserEmailComfirmation\UserEmailConfirmationService;

class UserEmailConfirmationController extends AbstractController
{
    private UserEmailConfirmationService $emailConfirmationService;

    public function __construct(UserEmailConfirmationService $userEmailConfirmationService)
    {
        $this->emailConfirmationService = $userEmailConfirmationService;
    }

    public function __invoke(UserEmailConfirmationRequestDto $request): JsonResponse
    {
        $user = $this->emailConfirmationService->__invoke(new UserEmailConfirmationInputDto($request->token));
        $response = new ResponseDto(message: 'User activated', data: ['id' => $user->id->getValue()]);

        return $this->json($response, Response::HTTP_CREATED);
    }
}
