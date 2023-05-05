<?php

declare(strict_types=1);

namespace Product\Adapter\Http\Controller\ProductRemove;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Ports\Security\UserSharedInterface;
use Common\Domain\Response\RESPONSE_STATUS;
use Common\Domain\Response\ResponseDto;
use Product\Adapter\Http\Controller\ProductRemove\Dto\ProductRemoveRequestDto;
use Product\Application\ProductRemove\Dto\ProductRemoveInputDto;
use Product\Application\ProductRemove\ProductRemoveUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductRemoveController extends AbstractController
{
    public function __construct(
        private ProductRemoveUseCase $ProductRemoveUseCase,
        private Security $security
    ) {
    }

    public function __invoke(ProductRemoveRequestDto $request): JsonResponse
    {
        $productRemoved = $this->ProductRemoveUseCase->__invoke(
            $this->createProductRemoveInputDto($request->groupId, $request->productId, $request->shopId)
        );

        return $this->createResponse($productRemoved);
    }

    private function createProductRemoveInputDto(string|null $groupId, string|null $productId, string|null $shopId): ProductRemoveInputDto
    {
        /** @var UserSharedInterface $userSharedAdapter */
        $userSharedAdapter = $this->security->getUser();

        return new ProductRemoveInputDto($userSharedAdapter->getUser(), $groupId, $productId, $shopId);
    }

    private function createResponse(ApplicationOutputInterface $productRemoved): JsonResponse
    {
        $responseDto = (new ResponseDto())
            ->setMessage('Product removed')
            ->setStatus(RESPONSE_STATUS::OK)
            ->setData($productRemoved->toArray());

        return new JsonResponse($responseDto, Response::HTTP_OK);
    }
}
