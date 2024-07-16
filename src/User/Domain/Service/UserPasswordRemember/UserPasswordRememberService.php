<?php

declare(strict_types=1);

namespace User\Domain\Service\UserPasswordRemember;

use Common\Domain\Model\ValueObject\String\Url;
use User\Domain\Model\User;
use User\Domain\Port\Repository\UserRepositoryInterface;
use User\Domain\Service\SendEmailPasswordRemember\Dto\SendEmailPasswordRememberDto;
use User\Domain\Service\SendEmailPasswordRemember\SendEmailPasswordRememberService;
use User\Domain\Service\UserPasswordRemember\Dto\UserPasswordRememberDto;

class UserPasswordRememberService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private SendEmailPasswordRememberService $sendEmailPasswordRememberService
    ) {
    }

    public function __invoke(UserPasswordRememberDto $passwordRememberDto): void
    {
        $user = $this->userRepository->findUserByEmailOrFail($passwordRememberDto->email);

        $this->sendEmailPasswordRememberService->__invoke(
            $this->createEmailDto($user, $passwordRememberDto->passwordRememberUrl)
        );
    }

    private function createEmailDto(User $user, Url $passwordRememberUrl): SendEmailPasswordRememberDto
    {
        return new SendEmailPasswordRememberDto(
            $user->getId(),
            $user->getEmail(),
            $user->getName(),
            $passwordRememberUrl
        );
    }
}
