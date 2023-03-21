<?php

declare(strict_types=1);

namespace User\Application\UserRegisterEmailConfirmation;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationInputDto;
use User\Application\UserRegisterEmailConfirmation\Dto\UserEmailConfirmationOutputDto;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfigurationJwtTokenHasExpiredException;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfirmationJwtTokenNotValidException;
use User\Application\UserRegisterEmailConfirmation\Exception\EmailConfirmationUserAlredyActiveException;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\Dto\EmailConfirmationJwtTokenValidationDto;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\EmailConfirmationJwtTokenValidationService;

class UserRegisterEmailConfirmationUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private EmailConfirmationJwtTokenValidationService $emailConfirmationJwtTokenValidationService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(UserEmailConfirmationInputDto $emailConfirmation): UserEmailConfirmationOutputDto
    {
        try {
            $this->validation($emailConfirmation);

            $user = $this->emailConfirmationJwtTokenValidationService->__invoke(
                $this->createEmailConfirmationJwtTokenValidationDto($emailConfirmation->token)
            );

            $this->eventsRegisteredDispatch($this->eventDispatcher, $user->getEventsRegistered());

            return new UserEmailConfirmationOutputDto($user->getId());
        } catch (JwtTokenExpiredException) {
            throw EmailConfigurationJwtTokenHasExpiredException::fromMessage('Token has expired');
        } catch (JwtException) {
            throw EmailConfirmationJwtTokenNotValidException::fromMessage('Wrong token');
        } catch (DBNotFoundException) {
            throw EmailConfirmationJwtTokenNotValidException::fromMessage('Wrong token');
        } catch (InvalidArgumentException) {
            throw EmailConfirmationUserAlredyActiveException::fromMessage('User already active');
        }
    }

    private function validation(UserEmailConfirmationInputDto $emailConfirmation)
    {
        $errorList = $emailConfirmation->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Wrong token', $errorList);
        }
    }

    private function createEmailConfirmationJwtTokenValidationDto(JwtToken $token): EmailConfirmationJwtTokenValidationDto
    {
        return new EmailConfirmationJwtTokenValidationDto($token);
    }
}
