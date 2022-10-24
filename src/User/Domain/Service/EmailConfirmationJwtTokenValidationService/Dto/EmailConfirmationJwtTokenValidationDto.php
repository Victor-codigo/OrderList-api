<?php

declare(strict_types=1);

namespace User\Domain\Service\EmailConfirmationJwtTokenValidationService\Dto;

use Common\Domain\Model\ValueObject\String\JwtToken;

class EmailConfirmationJwtTokenValidationDto
{
    public readonly JwtToken|null $token;

    public function __construct(JwtToken|null $token)
    {
        $this->token = $token;
    }
}
