<?php

declare(strict_types=1);

namespace Product\Application\SetProductShopPrice;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\SetProductShopPrice\Dto\SetProductShopPriceInputDto;
use Product\Application\SetProductShopPrice\Dto\SetProductShopPriceOutputDto;
use Product\Application\SetProductShopPrice\Exception\SetProductShopPriceValidateGroupAndUserException;
use Product\Domain\Model\ProductShop;
use Product\Domain\Service\SetProductShopPrice\Dto\SetProductShopPriceDto;
use Product\Domain\Service\SetProductShopPrice\SetProductShopPriceService;

class SetProductShopPriceUseCase extends ServiceBase
{
    public function __construct(
        private SetProductShopPriceService $setProductShopPriceService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(SetProductShopPriceInputDto $input): SetProductShopPriceOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productShopModified = $this->setProductShopPriceService->__invoke(
                $this->createSetProductShopPriceDto($input)
            );

            return $this->createSetProductShopPriceOutputDto($input->groupId, $productShopModified);
        } catch (ValidateGroupAndUserException) {
            throw SetProductShopPriceValidateGroupAndUserException::fromMessage('You have no permissions');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(SetProductShopPriceInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createSetProductShopPriceDto(SetProductShopPriceInputDto $input): SetProductShopPriceDto
    {
        return new SetProductShopPriceDto($input->groupId, $input->productId, $input->shopId, $input->productsOrShopsId, $input->prices);
    }

    /**
     * @param ProductShop[] $productShopModified
     */
    private function createSetProductShopPriceOutputDto(Identifier $groupId, array $productShopModified): SetProductShopPriceOutputDto
    {
        return new SetProductShopPriceOutputDto($groupId, $productShopModified);
    }
}
