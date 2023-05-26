<?php

declare(strict_types=1);

namespace Product\Application\ProductSetShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductSetShopPrice\Dto\ProductSetShopPriceInputDto;
use Product\Application\ProductSetShopPrice\Dto\ProductSetShopPriceOutputDto;
use Product\Application\ProductSetShopPrice\Exception\ProductSetShopPriceProductNotFoundException;
use Product\Application\ProductSetShopPrice\Exception\ProductSetShopPriceValidateGroupAndUserException;
use Product\Domain\Model\ProductShop;
use Product\Domain\Service\ProductSetShopPrice\Dto\ProductSetShopPriceDto;
use Product\Domain\Service\ProductSetShopPrice\ProductSetShopPriceService;

class ProductSetShopPriceUseCase extends ServiceBase
{
    public function __construct(
        private ProductSetShopPriceService $productSetShopPriceService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ProductSetShopPriceInputDto $input): ProductSetShopPriceOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productShopModified = $this->productSetShopPriceService->__invoke(
                $this->createProductSetShopPriceDto($input)
            );

            return $this->createProductSetShopPriceOutputDto($productShopModified);
        } catch (ValidateGroupAndUserException) {
            throw ProductSetShopPriceValidateGroupAndUserException::fromMessage('You have no permissions');
        } catch (DBNotFoundException) {
            throw ProductSetShopPriceProductNotFoundException::fromMessage('Product not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ProductSetShopPriceInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductSetShopPriceDto(ProductSetShopPriceInputDto $input): ProductSetShopPriceDto
    {
        return new ProductSetShopPriceDto($input->productId, $input->shopId, $input->groupId, $input->price);
    }

    private function createProductSetShopPriceOutputDto(ProductShop $productShopModified): ProductSetShopPriceOutputDto
    {
        return new ProductSetShopPriceOutputDto($productShopModified);
    }
}
