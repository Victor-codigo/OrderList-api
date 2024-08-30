<?php

declare(strict_types=1);

namespace Module\Application\Endpoint\Dto;

use Common\Domain\Service\ServiceInputDtoInterface;
use Common\Domain\Validation\ValidationInterface;

class EndpointInputDto implements ServiceInputDtoInterface
{
    #[\Override]
    public function validate(ValidationInterface $validator): array
    {
    }
}
