<?php

declare(strict_types=1);

namespace Product\Application\ProductRemoveAllGroupsProducts;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsInputDto;
use Product\Application\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsOutputDto;
use Product\Application\ProductRemoveAllGroupsProducts\Exception\ProductRemoveAllGroupsProductsNotFoundException;
use Product\Application\ProductRemoveAllGroupsProducts\Exception\ProductRemoveAllGroupsProductsSystemKeyException;
use Product\Domain\Service\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsDto;
use Product\Domain\Service\ProductRemoveAllGroupsProducts\ProductRemoveAllGroupsProductsService;

class ProductRemoveAllGroupsProductsUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private ProductRemoveAllGroupsProductsService $productRemoveAllGroupsProductsService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(ProductRemoveAllGroupsProductsInputDto $input): ProductRemoveAllGroupsProductsOutputDto
    {
        $this->validation($input);

        try {
            $productRemovedId = $this->productRemoveAllGroupsProductsService->__invoke(
                $this->createProductRemoveAllGroupsProductsDto($input->groupsId)
            );

            return $this->createProductRemoveAllGroupsProductsOutputDto($productRemovedId);
        } catch (DBNotFoundException) {
            throw ProductRemoveAllGroupsProductsNotFoundException::fromMessage('Products not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ProductRemoveAllGroupsProductsInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($this->systemKey !== $input->systemKey) {
            throw ProductRemoveAllGroupsProductsSystemKeyException::fromMessage('Wrong system key');
        }
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createProductRemoveAllGroupsProductsDto(array $groupsId): ProductRemoveAllGroupsProductsDto
    {
        return new ProductRemoveAllGroupsProductsDto($groupsId);
    }

    /**
     * @param Identifier[] $productsIdRemoved
     */
    private function createProductRemoveAllGroupsProductsOutputDto(array $productsIdRemoved): ProductRemoveAllGroupsProductsOutputDto
    {
        return new ProductRemoveAllGroupsProductsOutputDto($productsIdRemoved);
    }
}
