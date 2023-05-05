<?php

declare(strict_types=1);

namespace Product\Application\ProductRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\ModuleCommunication\ModuleCommunicationFactory;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
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
        private ValidationInterface $validator
    ) {
    }

    public function __invoke(ProductRemoveInputDto $input): ProductRemoveOutputDto
    {
        $this->validation($input);

        try {
            $productRemovedId = $this->productRemoveService->__invoke(
                $this->createProductRemoveDto($input->groupId, $input->productId, $input->shopId)
            );

            return $this->createProductRemoveOutputDto($productRemovedId);
        } catch (DBNotFoundException) {
            throw ProductRemoveProductNotFoundException::fromMessage('Product not found');
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

        $this->validateGroupAndUser($input->groupId, $input->userSession->getId());
    }

    /**
     * @throws ProductRemoveGroupOrUserNotValidException
     */
    private function validateGroupAndUser(Identifier $groupId, Identifier $userSessionId): void
    {
        $page = ValueObjectFactory::createPaginatorPage(1);
        $pageItems = ValueObjectFactory::createPaginatorPageItems(1);

        $response = $this->moduleCommunication->__invoke(
            ModuleCommunicationFactory::groupGetUsers($groupId, $page, $pageItems)
        );

        if (!empty($response->getErrors()) || !$response->hasContent()) {
            throw ProductRemoveGroupOrUserNotValidException::fromMessage('You have not permissions');
        }
    }

    private function createProductRemoveDto(Identifier $groupId, Identifier $productId, Identifier $shopId): ProductRemoveDto
    {
        return new ProductRemoveDto($productId, $groupId, $shopId);
    }

    private function createProductRemoveOutputDto(Identifier $productRemovedId): ProductRemoveOutputDto
    {
        return new ProductRemoveOutputDto($productRemovedId);
    }
}
