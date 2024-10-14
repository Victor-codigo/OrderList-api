<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetFirstLetter;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopGetFirstLetter\Dto\ShopGetFirstLetterInputDto;
use Shop\Application\ShopGetFirstLetter\Dto\ShopGetFirstLetterOutputDto;
use Shop\Application\ShopGetFirstLetter\Exception\ShopGetFirstLetterShopsNotFoundException;
use Shop\Application\ShopGetFirstLetter\Exception\ShopGetFirstLetterValidateGroupAndUserException;
use Shop\Domain\Service\ShopGetFirstLetter\Dto\ShopGetFirstLetterDto;
use Shop\Domain\Service\ShopGetFirstLetter\ShopGetFirstLetterService;

class ShopGetFirstLetterUseCase extends ServiceBase
{
    public function __construct(
        private ShopGetFirstLetterService $shopGetFirstLetterService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService,
    ) {
    }

    public function __invoke(ShopGetFirstLetterInputDto $input): ShopGetFirstLetterOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $shopsFirstLetter = $this->shopGetFirstLetterService->__invoke(
                $this->createShopGetFirstLetterDto($input)
            );

            return $this->createShopGetFirstLetterOutputDto($shopsFirstLetter);
        } catch (ValidateGroupAndUserException) {
            throw ShopGetFirstLetterValidateGroupAndUserException::fromMessage('You have not permissions');
        } catch (DBNotFoundException) {
            throw ShopGetFirstLetterShopsNotFoundException::fromMessage('No shops found');
        } catch (\Throwable) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ShopGetFirstLetterInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createShopGetFirstLetterDto(ShopGetFirstLetterInputDto $input): ShopGetFirstLetterDto
    {
        return new ShopGetFirstLetterDto($input->groupId);
    }

    /**
     * @param array<int, string> $shopsFirstLetter
     */
    private function createShopGetFirstLetterOutputDto(array $shopsFirstLetter): ShopGetFirstLetterOutputDto
    {
        return new ShopGetFirstLetterOutputDto($shopsFirstLetter);
    }
}
