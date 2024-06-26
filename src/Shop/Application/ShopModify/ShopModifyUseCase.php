<?php

declare(strict_types=1);

namespace Shop\Application\ShopModify;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\FileUpload\Exception\FileUploadReplaceException;
use Common\Domain\FileUpload\Exception\File\FileException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopModify\Dto\ShopModifyInputDto;
use Shop\Application\ShopModify\Dto\ShopModifyOutputDto;
use Shop\Application\ShopModify\Exception\ShopModifyImageException;
use Shop\Application\ShopModify\Exception\ShopModifyShopNameIsAlreadyInUseException;
use Shop\Application\ShopModify\Exception\ShopModifyShopNotFoundException;
use Shop\Application\ShopModify\Exception\ShopModifyValidateGroupAndUserException;
use Shop\Domain\Service\ShopModify\Dto\ShopModifyDto;
use Shop\Domain\Service\ShopModify\Exception\ShopModifyNameIsAlreadyInDataBaseException;
use Shop\Domain\Service\ShopModify\ShopModifyService;

class ShopModifyUseCase extends ServiceBase
{
    public function __construct(
        private ShopModifyService $ShopModifyService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ShopModifyInputDto $input): ShopModifyOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);
            $this->ShopModifyService->__invoke(
                $this->createShopModifyDto($input)
            );

            return $this->createShopModifyOutputDto($input->shopId);
        } catch (ValidateGroupAndUserException) {
            throw ShopModifyValidateGroupAndUserException::fromMessage('User does not belong to the group');
        } catch (DBNotFoundException) {
            throw ShopModifyShopNotFoundException::fromMessage('The shop id not found');
        } catch (ShopModifyNameIsAlreadyInDataBaseException) {
            throw ShopModifyShopNameIsAlreadyInUseException::fromMessage('Shop name is already in use');
        } catch (FileException|FileUploadReplaceException) {
            throw ShopModifyImageException::fromMessage('Image error');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ShopModifyInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createShopModifyDto(ShopModifyInputDto $input): ShopModifyDto
    {
        return new ShopModifyDto(
            $input->shopId,
            $input->groupId,
            $input->name,
            $input->address,
            $input->description,
            $input->image,
            $input->imageRemove
        );
    }

    private function createShopModifyOutputDto(Identifier $shopId): ShopModifyOutputDto
    {
        return new ShopModifyOutputDto($shopId);
    }
}
