<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRememberChange;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use InvalidArgumentException;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangePasswordNewAndPasswrodNewRepeatAreNotEqualsException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeTokenExpiredException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeTokenWrongException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeUserNotFoundException;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\Exception\PasswordNewAndRepeatAreNotTheSameException;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService;

class UserPasswordRememberChangeService extends ServiceBase
{
    private UserPasswordChangeService $userPasswordChangeService;
    private JwtHS256Interface  $jwt;
    private ValidationInterface  $validator;

    public function __construct(
        UserPasswordChangeService $userPasswordChangeService,
        JwtHS256Interface $jwt,
        ValidationInterface $validator
    ) {
        $this->userPasswordChangeService = $userPasswordChangeService;
        $this->jwt = $jwt;
        $this->validator = $validator;
    }

    public function __invoke(UserPasswordRememberChangeInputDto $passwordChangeDto)
    {
        $this->validation($passwordChangeDto);

        try {
            $tokenDecoded = $this->getToken($passwordChangeDto->token);

            $this->userPasswordChangeService->__invoke(
                $this->createUserPasswordChangeDto($tokenDecoded->username, $passwordChangeDto->passwordNew, $passwordChangeDto->passwordNewRepeat)
            );
        } catch (InvalidArgumentException) {
            throw UserPasswordRememberChangeTokenWrongException::fromMessage('Wrong token');
        } catch (JwtTokenExpiredException) {
            throw UserPasswordRememberChangeTokenExpiredException::fromMessage('Token has expired');
        } catch (JwtException) {
            throw UserPasswordRememberChangeTokenWrongException::fromMessage('Wrong token');
        } catch (DBNotFoundException) {
            throw UserPasswordRememberChangeUserNotFoundException::fromMessage('It could not change password');
        } catch (PasswordNewAndRepeatAreNotTheSameException) {
            throw UserPasswordRememberChangePasswordNewAndPasswrodNewRepeatAreNotEqualsException::fromMessage('Password new and Repeat new are not equals');
        }
    }

    private function createUserPasswordChangeDto(string $id, Password $passwordNew, Password $passwordNewRepeat): UserPasswordChangeDto
    {
        return new UserPasswordChangeDto(
            ValueObjectFactory::createIdentifier($id),
            ValueObjectFactory::createPassword(null),
            $passwordNew,
            $passwordNewRepeat,
            false
        );
    }

    private function validation(UserPasswordRememberChangeInputDto $passwordChangeDto): void
    {
        $errorList = $passwordChangeDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Wrong password', $errorList);
        }
    }

    private function getToken(JwtToken $token): \stdClass
    {
        $tokenDecoded = $this->jwt->decode($token->getValue());

        if ($this->jwt->hasExpired($tokenDecoded)) {
            throw JwtTokenExpiredException::fromMessage('Token has expired');
        }

        return $tokenDecoded;
    }
}
