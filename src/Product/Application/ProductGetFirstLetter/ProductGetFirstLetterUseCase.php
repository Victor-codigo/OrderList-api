<?php

declare(strict_types=1);

namespace Product\Application\ProductGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductGetFirstLetter\Dto\ProductGetFirstLetterInputDto;
use Product\Application\ProductGetFirstLetter\Dto\ProductGetFirstLetterOutputDto;
use Product\Application\ProductGetFirstLetter\Exception\ProductGetFirstLetterProductsNotFoundException;
use Product\Application\ProductGetFirstLetter\Exception\ProductGetFirstLetterValidateGroupAndUserException;
use Product\Domain\Service\ProductGetFirstLetter\Dto\ProductGetFirstLetterDto;
use Product\Domain\Service\ProductGetFirstLetter\ProductGetFirstLetterService;

class ProductGetFirstLetterUseCase extends ServiceBase
{
    public function __construct(
        private ProductGetFirstLetterService $productGetFirstLetterService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    public function __invoke(ProductGetFirstLetterInputDto $input): ProductGetFirstLetterOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productsFirstLetter = $this->productGetFirstLetterService->__invoke(
                $this->createProductGetFirstLetterDto($input)
            );

            return $this->createProductGetFirstLetterOutputDto($productsFirstLetter);
        } catch (ValidateGroupAndUserException) {
            throw ProductGetFirstLetterValidateGroupAndUserException::fromMessage('You have not permissions');
        } catch (DBNotFoundException) {
            throw ProductGetFirstLetterProductsNotFoundException::fromMessage('No products found');
        } catch (\Throwable) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ProductGetFirstLetterInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductGetFirstLetterDto(ProductGetFirstLetterInputDto $input): ProductGetFirstLetterDto
    {
        return new ProductGetFirstLetterDto($input->groupId);
    }

    /**
     * @param string[] $productsFirstLetter
     */
    private function createProductGetFirstLetterOutputDto(array $productsFirstLetter): ProductGetFirstLetterOutputDto
    {
        return new ProductGetFirstLetterOutputDto($productsFirstLetter);
    }
}
