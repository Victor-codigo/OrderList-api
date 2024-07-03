<?php

declare(strict_types=1);

namespace Product\Application\ProductModify;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductModify\Dto\ProductModifyInputDto;
use Product\Application\ProductModify\Dto\ProductModifyOutputDto;
use Product\Application\ProductModify\Exception\ProductModifyImageException;
use Product\Application\ProductModify\Exception\ProductModifyProductNameRepeatedException;
use Product\Application\ProductModify\Exception\ProductModifyProductNotFoundException;
use Product\Application\ProductModify\Exception\ProductModifyValidateGroupAndUserException;
use Product\Domain\Service\ProductModify\Dto\ProductModifyDto;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNameIsAlreadyInDataBaseException;
use Product\Domain\Service\ProductModify\Exception\ProductModifyProductNotFoundException as ProductModifyProductNotFoundExceptionDomain;
use Product\Domain\Service\ProductModify\ProductModifyService;

class ProductModifyUseCase extends ServiceBase
{
    public function __construct(
        private ProductModifyService $ProductModifyService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ProductModifyInputDto $input): ProductModifyOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);
            $this->ProductModifyService->__invoke(
                $this->createProductModifyDto($input)
            );

            return $this->createProductModifyOutputDto($input->productId);
        } catch (ValidateGroupAndUserException) {
            throw ProductModifyValidateGroupAndUserException::fromMessage('User does not belong to the group');
        } catch (ProductModifyProductNameIsAlreadyInDataBaseException) {
            throw ProductModifyProductNameRepeatedException::fromMessage('Product name repeated');
        } catch (ProductModifyProductNotFoundExceptionDomain) {
            throw ProductModifyProductNotFoundException::fromMessage('Product not found');
        } catch (FileException|FileUploadReplaceException) {
            throw ProductModifyImageException::fromMessage('Image error');
        } catch (\Throwable) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ProductModifyInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductModifyDto(ProductModifyInputDto $input): ProductModifyDto
    {
        return new ProductModifyDto(
            $input->productId,
            $input->groupId,
            $input->name,
            $input->description,
            $input->image,
            $input->imageRemove
        );
    }

    private function createProductModifyOutputDto(Identifier $productId): ProductModifyOutputDto
    {
        return new ProductModifyOutputDto($productId);
    }
}
