<?php

declare(strict_types=1);

namespace Module\Application\Endpoint;

use Common\Domain\Service\ServiceBase;
use Module\Application\Endpoint\Dto\EndpointInputDto;
use Module\Domain\Service\Endpoint\Dto\EndpointDto;

class EndpointUseCase extends ServiceBase
{
    public function __construct(
    ) {
    }

    public function __invoke(EndpointInputDto $input)
    {
    }

    private function validation(EndpointInputDto $input): void
    {
    }

    private function createEndpointDto(EndpointInputDto $input): EndpointDto
    {
    }
}
