<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordRemember;

use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\SendEmailPasswordRemember\Dto\SendEmailPasswordRememberDto;
use User\Domain\Service\SendEmailPasswordRemember\SendEmailPasswordRememberService;
use User\Domain\Service\UserPasswordRemember\Dto\UserPasswordRememberDto;

class UserPasswordRememberService
{
    private UserRepositoryInterface $userRepository;
    private SendEmailPasswordRememberService $sendEmailPasswordRememberService;

    public function __construct(UserRepositoryInterface $userRepository, SendEmailPasswordRememberService $sendEmailPasswordRememberService)
    {
        $this->userRepository = $userRepository;
        $this->sendEmailPasswordRememberService = $sendEmailPasswordRememberService;
    }

    public function __invoke(UserPasswordRememberDto $passwordRememberDto)
    {
        $user = $this->userRepository->findUserByEmailOrFail($passwordRememberDto->email);

        $this->sendEmailPasswordRememberService->__invoke(
            $this->createEmailDto($user)
        );
    }

    private function createEmailDto(User $user): SendEmailPasswordRememberDto
    {
        return new SendEmailPasswordRememberDto($user->getId(), $user->getEmail(), $user->getName());
    }
}
