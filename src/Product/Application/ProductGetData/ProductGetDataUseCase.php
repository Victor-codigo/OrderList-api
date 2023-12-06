<?php

declare(strict_types=1);

namespace Product\Application\ProductGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductGetData\Dto\ProductGetDataInputDto;
use Product\Application\ProductGetData\Dto\ProductGetDataOutputDto;
use Product\Application\ProductGetData\Exception\ProductGetDataProductsNotFoundException;
use Product\Application\ProductGetData\Exception\ProductGetDataValidateGroupAndUserException;
use Product\Domain\Service\ProductGetData\Dto\ProductGetDataDto;
use Product\Domain\Service\ProductGetData\ProductGetDataService;

class ProductGetDataUseCase extends ServiceBase
{
    public function __construct(
        private ProductGetDataService $productGetDataService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ProductGetDataInputDto $input): ProductGetDataOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productsData = $this->productGetDataService->__invoke(
                $this->createProductGetDataDto($input)
            );

            return $this->createProductGetDataOutputDto($productsData, $input->page, $this->productGetDataService->getPagesTotal());
        } catch (ValidateGroupAndUserException) {
            throw ProductGetDataValidateGroupAndUserException::fromMessage('You have not permissions');
        } catch (DBNotFoundException) {
            throw ProductGetDataProductsNotFoundException::fromMessage('No products found');
        } catch (\Throwable $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ProductGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductGetDataDto(ProductGetDataInputDto $input): ProductGetDataDto
    {
        return new ProductGetDataDto(
            $input->groupId,
            $input->productId,
            $input->shopId,
            $input->productName,
            $input->productNameFilter,
            $input->shopNameFilter,
            $input->orderAsc,
            $input->page,
            $input->pageItems
        );
    }

    private function createProductGetDataOutputDto(array $productsData, PaginatorPage $page, int $pagesTotal): ProductGetDataOutputDto
    {
        return new ProductGetDataOutputDto($page, $pagesTotal, $productsData);
    }
}
