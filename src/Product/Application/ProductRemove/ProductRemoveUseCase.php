<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductRemove\Dto\ProductRemoveInputDto;
use Product\Application\ProductRemove\Dto\ProductRemoveOutputDto;
use Product\Application\ProductRemove\Exception\ProductRemoveGroupOrUserNotValidException;
use Product\Application\ProductRemove\Exception\ProductRemoveProductNotFoundException;
use Product\Domain\Service\ProductRemove\Dto\ProductRemoveDto;
use Product\Domain\Service\ProductRemove\ProductRemoveService;

class ProductRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ProductRemoveService $productRemoveService,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ProductRemoveInputDto $input): ProductRemoveOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $productsRemovedId = $this->productRemoveService->__invoke(
                $this->createProductRemoveDto($input->groupId, $input->productsId, $input->shopsId)
            );

            return $this->createProductRemoveOutputDto($productsRemovedId);
        } catch (DBNotFoundException) {
            throw ProductRemoveProductNotFoundException::fromMessage('Product not found');
        } catch (ValidateGroupAndUserException) {
            throw ProductRemoveGroupOrUserNotValidException::fromMessage('You have not permissions');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ProductRemoveGroupOrUserNotValidException
     */
    private function validation(ProductRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     */
    private function createProductRemoveDto(Identifier $groupId, array $productsId, array $shopsId): ProductRemoveDto
    {
        return new ProductRemoveDto($groupId, $productsId, $shopsId);
    }

    /**
     * @param Identifier[] $productsRemovedId
     */
    private function createProductRemoveOutputDto(array $productsRemovedId): ProductRemoveOutputDto
    {
        return new ProductRemoveOutputDto($productsRemovedId);
    }
}
