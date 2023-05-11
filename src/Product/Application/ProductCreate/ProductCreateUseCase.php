<?php

declare(strict_types=1);

namespace Product\Application\ProductCreate;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Product\Application\ProductCreate\Dto\ProductCreateInputDto;
use Product\Application\ProductCreate\Dto\ProductCreateOutputDto;
use Product\Application\ProductCreate\Exception\ProductCreateCanNotUploadFileException;
use Product\Application\ProductCreate\Exception\ProductCreateGroupException;
use Product\Application\ProductCreate\Exception\ProductCreateNameAlreadyExistsException;
use Product\Domain\Service\ProductCreate\Dto\ProductCreateDto;
use Product\Domain\Service\ProductCreate\Exception\ProductCreateNameAlreadyExistsException as ProductCreateNameAlreadyExistsExceptionService;
use Product\Domain\Service\ProductCreate\ProductCreateService;

class ProductCreateUseCase extends ServiceBase
{
    public function __construct(
        private ProductCreateService $ProductCreateService,
        private ValidationInterface $validator,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ProductCreateGroupException
     * @throws ProductCreateNameAlreadyExistsException
     * @throws ProductCreateCanNotUploadFileException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ProductCreateInputDto $input): ProductCreateOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $product = $this->ProductCreateService->__invoke(
                $this->createProductCreateDto($input)
            );

            return $this->createProductCreateOutputDto($product->getId());
        } catch (ProductCreateNameAlreadyExistsExceptionService) {
            throw ProductCreateNameAlreadyExistsException::fromMessage('Product name already exists');
        } catch (FileUploadException) {
            throw ProductCreateCanNotUploadFileException::fromMessage('An error occurred while file was uploading');
        } catch (ValidateGroupAndUserException) {
            throw ProductCreateGroupException::fromMessage('Error validating the group');
        } catch (\Exception $e) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ProductCreateGroupException
     */
    private function validation(ProductCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createProductCreateDto(ProductCreateInputDto $input): ProductCreateDto
    {
        return new ProductCreateDto($input->groupId, $input->name, $input->description, $input->image);
    }

    private function createProductCreateOutputDto(Identifier $productId): ProductCreateOutputDto
    {
        return new ProductCreateOutputDto($productId);
    }
}
