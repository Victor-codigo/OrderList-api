<?php

declare(strict_types=1);

namespace HealthCheck\Adapter\Http\HealthCheck;

use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use ListOrders\Adapter\Http\Controller\ListOrdersCreate\Dto\ListOrdersCreateRequestDto;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('HealthCheck')]
#[OA\Get(
    description: 'Check if API is up and running',
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The API is up and running',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'API is up'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
    ]
)]
class HealthCheckController extends AbstractController
{
    public function __invoke(ListOrdersCreateRequestDto $request): JsonResponse
    {
        return $this->createResponse();
    }

    private function createResponse(): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('API is up')
            ->setStatus(RESPONSE_STATUS::OK);

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
