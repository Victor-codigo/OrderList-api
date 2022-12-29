<?php

declare(strict_types=1);

namespace User\Application\UserEmailComfirmation;

use Common\Adapter\Jwt\Exception\JwtException;
use Common\Adapter\Jwt\Exception\JwtTokenExpiredException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use User\Application\UserEmailComfirmation\Dto\UserEmailConfirmationInputDto;
use User\Application\UserEmailComfirmation\Dto\UserEmailConfirmationOutputDto;
use User\Application\UserEmailComfirmation\Exception\EmailConfigurationJwtTokenHasExpiredException;
use User\Application\UserEmailComfirmation\Exception\EmailConfirmationJwtTokenNotValidException;
use User\Application\UserEmailComfirmation\Exception\EmailConfirmationUserAlredyActiveException;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\Dto\EmailConfirmationJwtTokenValidationDto;
use User\Domain\Service\EmailConfirmationJwtTokenValidationService\EmailConfirmationJwtTokenValidationService;

class UserEmailConfirmationUseCase extends ServiceBase
{
    private ValidationInterface $validator;
    private EmailConfirmationJwtTokenValidationService $emailConfirmationJwTTokenValidationService;

    public function __construct(ValidationInterface $validator, EmailConfirmationJwtTokenValidationService $emailConfirmationJwtTokenValidationService)
    {
        $this->validator = $validator;
        $this->emailConfirmationJwTTokenValidationService = $emailConfirmationJwtTokenValidationService;
    }

    public function __invoke(UserEmailConfirmationInputDto $emailConfirmation): UserEmailConfirmationOutputDto
    {
        try {
            $this->validation($emailConfirmation);

            $userId = $this->emailConfirmationJwTTokenValidationService->__invoke(
                $this->createEmailConfirmationJwtTokenValidationDto($emailConfirmation->token)
            );

            return new UserEmailConfirmationOutputDto($userId);
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
