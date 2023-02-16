<?php

declare(strict_types=1);

namespace Module\Adapter\Http\Controller\Endpoint;

use Module\Adapter\Http\Controller\Endpoint\Dto\EndpointRequestDto;
use Module\Application\Endpoint\Dto\EndpointInputDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EndpointController extends AbstractController
{
    public function __construct(
    ) {
    }

    public function __invoke(EndpointRequestDto $request): JsonResponse
    {
    }

    private function createEndpointInputDto(): EndpointInputDto
    {
    }

    private function createResponse(): JsonResponse
    {
    }
}
