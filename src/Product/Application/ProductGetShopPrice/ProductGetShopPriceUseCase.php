<?php

declare(strict_types=1);

namespace Product\Application\ProductGetShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductGetShopPrice\Dto\ProductGetShopPriceInputDto;
use Product\Application\ProductGetShopPrice\Dto\ProductGetShopPriceOutputDto;
use Product\Application\ProductGetShopPrice\Exception\ProductGetShopPriceProductNotFoundException;
use Product\Application\ProductGetShopPrice\Exception\ProductGetShopPriceValidateGroupAndUserException;
use Product\Domain\Service\ProductGetShopPrice\Dto\ProductGetShopPriceDto;
use Product\Domain\Service\ProductGetShopPrice\ProductGetShopPriceService;

class ProductGetShopPriceUseCase extends ServiceBase
{
    public function __construct(
        private ProductGetShopPriceService $ProductGetShopPriceService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ProductGetShopPriceValidateGroupAndUserException
     * @throws ProductGetShopPriceProductNotFoundException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ProductGetShopPriceInputDto $input): ProductGetShopPriceOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productsShops = $this->ProductGetShopPriceService->__invoke(
                $this->createProductGetShopPriceDto($input)
            );

            return $this->createProductGetShopPriceOutputDto($productsShops);
        } catch (ValidateGroupAndUserException) {
            throw ProductGetShopPriceValidateGroupAndUserException::fromMessage('You not belong to the group');
        } catch (DBNotFoundException) {
            throw ProductGetShopPriceProductNotFoundException::fromMessage('Product not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ProductGetShopPriceInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductGetShopPriceDto(ProductGetShopPriceInputDto $input): ProductGetShopPriceDto
    {
        return new ProductGetShopPriceDto($input->productsId, $input->shopsId, $input->groupId);
    }

    private function createProductGetShopPriceOutputDto(array $productsShops): ProductGetShopPriceOutputDto
    {
        return new ProductGetShopPriceOutputDto($productsShops);
    }
}
