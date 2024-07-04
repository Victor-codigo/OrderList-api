<?php

declare(strict_types=1);

namespace Module\Application\Endpoint;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Module\Application\Endpoint\Dto\EndpointInputDto;
use Module\Application\Endpoint\Dto\EndpointOutputDto;
use Module\Domain\Service\Endpoint\Dto\EndpointDto;
use Module\Domain\Service\Endpoint\EndpointService;

class EndpointUseCase extends ServiceBase
{
    public function __construct(
        private EndpointService $EndpointService,
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(EndpointInputDto $input): EndpointOutputDto
    {
        $this->validation($input);

        try {
            $this->EndpointService->__invoke(
                $this->createEndpointDto()
            );

            return $this->createEndpointOutputDto();
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(EndpointInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createEndpointDto(): EndpointDto
    {
        return new EndpointDto();
    }

    private function createEndpointOutputDto(): EndpointOutputDto
    {
        return new EndpointOutputDto();
    }
}
