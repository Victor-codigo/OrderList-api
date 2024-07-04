<?php

declare(strict_types=1);

namespace Shop\Application\ShopCreate;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\FileUpload\Exception\FileUploadException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopCreate\Dto\ShopCreateInputDto;
use Shop\Application\ShopCreate\Dto\ShopCreateOutputDto;
use Shop\Application\ShopCreate\Exception\ShopCreateCanNotUploadFileException;
use Shop\Application\ShopCreate\Exception\ShopCreateGroupException;
use Shop\Application\ShopCreate\Exception\ShopCreateNameAlreadyExistsException;
use Shop\Domain\Service\ShopCreate\Dto\ShopCreateDto;
use Shop\Domain\Service\ShopCreate\Exception\ShopCreateNameAlreadyExistsException as ShopCreateNameAlreadyExistsExceptionService;
use Shop\Domain\Service\ShopCreate\ShopCreateService;

class ShopCreateUseCase extends ServiceBase
{
    public function __construct(
        private ShopCreateService $shopCreateService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ShopCreateGroupException
     * @throws ShopCreateNameAlreadyExistsException
     * @throws ShopCreateCanNotUploadFileException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ShopCreateInputDto $input): ShopCreateOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $shop = $this->shopCreateService->__invoke(
                $this->createShopCreateDto($input)
            );

            return $this->createShopCreateOutputDto($shop->getId());
        } catch (ShopCreateNameAlreadyExistsExceptionService) {
            throw ShopCreateNameAlreadyExistsException::fromMessage('Shop name already exists');
        } catch (FileUploadException) {
            throw ShopCreateCanNotUploadFileException::fromMessage('An error occurred while file was uploading');
        } catch (ValidateGroupAndUserException) {
            throw ShopCreateGroupException::fromMessage('Error validating the group');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ShopCreateGroupException
     */
    private function validation(ShopCreateInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createShopCreateDto(ShopCreateInputDto $input): ShopCreateDto
    {
        return new ShopCreateDto($input->groupId, $input->name, $input->address, $input->description, $input->image);
    }

    private function createShopCreateOutputDto(Identifier $shopId): ShopCreateOutputDto
    {
        return new ShopCreateOutputDto($shopId);
    }
}
