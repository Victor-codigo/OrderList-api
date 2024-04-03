<?php

declare(strict_types=1);

namespace Order\Adapter\Http\Controller\OrderBought;

use Common\Adapter\Security\UserSharedSymfonyAdapter;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use OpenApi\Attributes as OA;
use Order\Adapter\Http\Controller\OrderBought\Dto\OrderBoughtRequestDto;
use Order\Application\OrderBought\Dto\OrderBoughtInputDto;
use Order\Application\OrderBought\OrderBoughtUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag('Order')]
#[OA\Patch(
    description: 'Sets if an order is bought',
    requestBody: new OA\RequestBody(
        required: true,
        content: [
            new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'order_id', type: 'string', description: 'Order id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'group_id', type: 'string', description: 'Group id', example: '0290bf7e-2e68-4698-ba2e-d2394c239572'),
                        new OA\Property(property: 'bought', type: 'bool', description: 'Mark the order as bought', example: 'true'),
                    ]
                )
            ),
        ]
    ),
    responses: [
        new OA\Response(
            response: Response::HTTP_OK,
            description: 'The order has been modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'message', type: 'string', example: 'Order bought set'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(default: '<id, array>')),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items()),
                    ]
                )
            )
        ),
        new OA\Response(
            response: Response::HTTP_BAD_REQUEST,
            description: 'The order could not be modified',
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'error'),
                        new OA\Property(property: 'message', type: 'string', example: 'Some error message'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items()),
                        new OA\Property(property: 'errors', type: 'array', items: new OA\Items(default: '<order_id|group_id|order_not_found|group_error, string|array>')),
                    ]
                )
            )
        ),
    ]
)]
class OrderBoughtController extends AbstractController
{
    public function __construct(
        private OrderBoughtUseCase $orderBoughtUseCase,
        private Security $security
    ) {
    }

    public function __invoke(OrderBoughtRequestDto $request): JsonResponse
    {
        $orderBought = $this->orderBoughtUseCase->__invoke(
            $this->createOrderBoughtInputDto(
                $request->orderId,
                $request->groupId,
                $request->bought,
            )
        );

        return $this->createResponse($orderBought);
    }

    private function createOrderBoughtInputDto(?string $orderId, ?string $groupId, bool $bought): OrderBoughtInputDto
    {
        /** @var UserSharedSymfonyAdapter $userAdapter */
        $userAdapter = $this->security->getUser();

        return new OrderBoughtInputDto(
            $userAdapter->getUser(),
            $orderId,
            $groupId,
            $bought,
        );
    }

    private function createResponse(ApplicationOutputInterface $orderBought): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Order bought set')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($orderBought->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
