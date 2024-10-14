<?php

declare(strict_types=1);

namespace Product\Application\GetProductShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\GetProductShopPrice\Dto\GetProductShopPriceInputDto;
use Product\Application\GetProductShopPrice\Dto\GetProductShopPriceOutputDto;
use Product\Application\GetProductShopPrice\Exception\GetProductShopPriceProductNotFoundException;
use Product\Application\GetProductShopPrice\Exception\GetProductShopPriceValidateGroupAndUserException;
use Product\Domain\Service\GetProductShopPrice\Dto\GetProductShopPriceDto;
use Product\Domain\Service\GetProductShopPrice\GetProductShopPriceService;

class GetProductShopPriceUseCase extends ServiceBase
{
    public function __construct(
        private GetProductShopPriceService $getProductShopPriceService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws GetProductShopPriceValidateGroupAndUserException
     * @throws GetProductShopPriceProductNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(GetProductShopPriceInputDto $input): GetProductShopPriceOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productsShops = $this->getProductShopPriceService->__invoke(
                $this->createGetProductShopPriceDto($input)
            );

            return $this->createGetProductShopPriceOutputDto($productsShops);
        } catch (ValidateGroupAndUserException) {
            throw GetProductShopPriceValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw GetProductShopPriceProductNotFoundException::fromMessage('Product not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(GetProductShopPriceInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createGetProductShopPriceDto(GetProductShopPriceInputDto $input): GetProductShopPriceDto
    {
        return new GetProductShopPriceDto($input->productsId, $input->shopsId, $input->groupId);
    }

    /**
     * @param array<int, array{
     *  product_id: string|null,
     *  shop_id: string|null,
     *  price: float|null,
     *  unit: string|null
     * }> $productsShops
     */
    private function createGetProductShopPriceOutputDto(array $productsShops): GetProductShopPriceOutputDto
    {
        return new GetProductShopPriceOutputDto($productsShops);
    }
}
