<?php

declare(strict_types=1);

namespace Module\Adapter\Http\Controller\Endpoint;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Module\Adapter\Http\Controller\Endpoint\Dto\EndpointRequestDto;
use Module\Application\Endpoint\Dto\EndpointInputDto;
use Module\Application\Endpoint\EndpointUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EndpointController extends AbstractController
{
    public function __construct(
        private EndpointUseCase $EndpointUseCase
    ) {
    }

    public function __invoke(EndpointRequestDto $request): JsonResponse
    {
        $this->EndpointUseCase->__invoke(
            $this->createEndpointInputDto()
        );

        return $this->createResponse();
    }

    private function createEndpointInputDto(): EndpointInputDto
    {
        return new EndpointInputDto();
    }

    private function createResponse(): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData();

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
