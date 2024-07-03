<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemoveAllGroupsShops;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsInputDto;
use Shop\Application\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsOutputDto;
use Shop\Application\ShopRemoveAllGroupsShops\Exception\ShopRemoveAllGroupsShopsNotFoundException;
use Shop\Application\ShopRemoveAllGroupsShops\Exception\ShopRemoveAllGroupsShopsSystemKeyException;
use Shop\Domain\Service\ShopRemoveAllGroupsShops\Dto\ShopRemoveAllGroupsShopsDto;
use Shop\Domain\Service\ShopRemoveAllGroupsShops\ShopRemoveAllGroupsShopsService;

class ShopRemoveAllGroupsShopsUseCase extends ServiceBase
{
    public function __construct(
        private ValidationInterface $validator,
        private ShopRemoveAllGroupsShopsService $shopRemoveAllGroupsShopsService,
        private ModuleCommunicationInterface $moduleCommunication,
        private string $systemKey
    ) {
    }

    public function __invoke(ShopRemoveAllGroupsShopsInputDto $input): ShopRemoveAllGroupsShopsOutputDto
    {
        $this->validation($input);

        try {
            $shopRemovedId = $this->shopRemoveAllGroupsShopsService->__invoke(
                $this->createShopRemoveAllGroupsShopsDto($input->groupsId)
            );

            return $this->createShopRemoveAllGroupsShopsOutputDto($shopRemovedId);
        } catch (DBNotFoundException) {
            throw ShopRemoveAllGroupsShopsNotFoundException::fromMessage('Shops not found');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ShopRemoveAllGroupsShopsInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($this->systemKey !== $input->systemKey) {
            throw ShopRemoveAllGroupsShopsSystemKeyException::fromMessage('Wrong system key');
        }
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createShopRemoveAllGroupsShopsDto(array $groupsId): ShopRemoveAllGroupsShopsDto
    {
        return new ShopRemoveAllGroupsShopsDto($groupsId);
    }

    /**
     * @param Identifier[] $shopsIdRemoved
     */
    private function createShopRemoveAllGroupsShopsOutputDto(array $shopsIdRemoved): ShopRemoveAllGroupsShopsOutputDto
    {
        return new ShopRemoveAllGroupsShopsOutputDto($shopsIdRemoved);
    }
}
