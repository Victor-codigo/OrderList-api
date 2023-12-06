<?php

declare(strict_types=1);

namespace Shop\Application\ShopGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopGetData\Dto\ShopGetDataInputDto;
use Shop\Application\ShopGetData\Dto\ShopGetDataOutputDto;
use Shop\Application\ShopGetData\Exception\ShopGetDataShopsNotFoundException;
use Shop\Application\ShopGetData\Exception\ShopGetDataValidateGroupAndUserException;
use Shop\Domain\Service\ShopGetData\Dto\ShopGetDataDto;
use Shop\Domain\Service\ShopGetData\ShopGetDataService;

class ShopGetDataUseCase extends ServiceBase
{
    public function __construct(
        private ShopGetDataService $shopGetDataService,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ShopGetDataInputDto $input): ShopGetDataOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $shopsData = $this->shopGetDataService->__invoke(
                $this->createShopGetDataDto($input)
            );

            return $this->createShopGetDataOutputDto($input->page, $this->shopGetDataService->getPagesTotal(), $shopsData);
        } catch (ValidateGroupAndUserException) {
            throw ShopGetDataValidateGroupAndUserException::fromMessage('You have not permissions');
        } catch (DBNotFoundException) {
            throw ShopGetDataShopsNotFoundException::fromMessage('No shops found');
        } catch (\Throwable) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    private function validation(ShopGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    private function createShopGetDataDto(ShopGetDataInputDto $input): ShopGetDataDto
    {
        return new ShopGetDataDto(
            $input->groupId,
            $input->shopsId,
            $input->productsId,
            $input->shopNameFilter,
            $input->shopName,
            $input->page,
            $input->pageItems,
            $input->orderAsc
        );
    }

    private function createShopGetDataOutputDto(PaginatorPage $page, int $pagesTotal, array $shopsData): ShopGetDataOutputDto
    {
        return new ShopGetDataOutputDto($page, $pagesTotal, $shopsData);
    }
}
