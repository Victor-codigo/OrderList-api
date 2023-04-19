<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRememberChange;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\JwtToken\JwtHS256Interface;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use InvalidArgumentException;
use User\Application\UserPasswordRememberChange\Dto\UserPasswordRememberChangeInputDto;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeNotificationException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangePasswordNewAndPasswrodNewRepeatAreNotEqualsException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeTokenExpiredException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeTokenWrongException;
use User\Application\UserPasswordRememberChange\Exception\UserPasswordRememberChangeUserNotFoundException;
use User\Domain\Service\UserPasswordChange\Dto\UserPasswordChangeDto;
use User\Domain\Service\UserPasswordChange\Exception\PasswordNewAndRepeatAreNotTheSameException;
use User\Domain\Service\UserPasswordChange\UserPasswordChangeService;

class UserPasswordRememberChangeUseCase extends ServiceBase
{
    public function __construct(
        private UserPasswordChangeService $userPasswordChangeService,
        private JwtHS256Interface $jwt,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey,
    ) {
    }

    /**
     * @throws UserPasswordRememberChangeTokenWrongException
     * @throws UserPasswordRememberChangeTokenExpiredException
     * @throws UserPasswordRememberChangeUserNotFoundException
     * @throws UserPasswordRememberChangePasswordNewAndPasswrodNewRepeatAreNotEqualsException
     */
    public function __invoke(UserPasswordRememberChangeInputDto $passwordChangeDto): void
    {
        $this->validation($passwordChangeDto);

        try {
            $tokenDecoded = $this->getToken($passwordChangeDto->token);

            $this->userPasswordChangeService->__invoke(
                $this->createUserPasswordChangeDto($tokenDecoded->username, $passwordChangeDto->passwordNew, $passwordChangeDto->passwordNewRepeat)
            );

            $this->createNotificationPasswordRemember($tokenDecoded->username, $this->systemKey);
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

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(UserPasswordRememberChangeInputDto $passwordChangeDto): void
    {
        $errorList = $passwordChangeDto->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Wrong password', $errorList);
        }
    }

    /**
     * @throws JwtTokenExpiredException
     */
    private function getToken(JwtToken $token): \stdClass
    {
        $tokenDecoded = $this->jwt->decode($token->getValue());

        if ($this->jwt->hasExpired($tokenDecoded)) {
            throw JwtTokenExpiredException::fromMessage('Token has expired');
        }

        return $tokenDecoded;
    }

    /**
     * @throws UserPasswordRememberChangeNotificationException
     */
    private function createNotificationPasswordRemember(string $userIdPlain, string $systemKey): void
    {
        $userId = ValueObjectFactory::createIdentifier($userIdPlain);
        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::notificationUserPasswordRemember($userId, $systemKey)
        );

        if (RESPONSE_STATUS::OK !== $response->getStatus()) {
            throw UserPasswordRememberChangeNotificationException::fromMessage('An error was ocurred when trying to send the notification: user password remember');
        }
    }
}
