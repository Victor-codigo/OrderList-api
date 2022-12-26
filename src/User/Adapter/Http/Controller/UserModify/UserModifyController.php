<?php

declare(strict_types=1);

namespace User\Adapter\Http\Controller\UserModify;

use Common\Adapter\FileUpload\UploadedFileSymfonyAdapter;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;
use User\Adapter\Http\Controller\UserModify\Dto\UserModifyRequestDto;
use User\Application\UserModify\Dto\UserModifyInputDto;
use User\Application\UserModify\UserModifyService;
use User\Domain\Port\User\UserInterface;

class UserModifyController extends AbstractController
{
    public function __construct(
        private UserModifyService $userModifyService,
        private Security $security
    ) {
    }

    public function __invoke(UserModifyRequestDto $request): JsonResponse
    {
        $this->userModifyService->__invoke(
            $this->createUserModifyInputDto($request)
        );

        $response = (new ResponseDto())
            ->setMessage('User modified')
            ->setStatus(RESPONSE_STATUS::OK);

        return $this->json($response);
    }

    private function createUserModifyInputDto(UserModifyRequestDto $requestDto): UserModifyInputDto
    {
        /** @var UserInterface */
        $user = $this->security->getUser();

        return UserModifyInputDto::create(
            $this->security->getUser()->getUserIdentifier(),
            $requestDto->name,
            $requestDto->imageRemove,
            null === $requestDto->image ? null : new UploadedFileSymfonyAdapter($requestDto->image),
            $user->getUser()
        );
    }
}
