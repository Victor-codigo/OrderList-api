<?php

declare(strict_types=1);

namespace Shop\Application\ShopRemove;

use Exception;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\ModuleCommunication\ModuleCommunicationInterface;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Service\ValidateGroupAndUser\Exception\ValidateGroupAndUserException;
use Common\Domain\Service\ValidateGroupAndUser\ValidateGroupAndUserService;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use Shop\Application\ShopRemove\Dto\ShopRemoveInputDto;
use Shop\Application\ShopRemove\Dto\ShopRemoveOutputDto;
use Shop\Application\ShopRemove\Exception\ShopRemoveGroupOrUserNotValidException;
use Shop\Application\ShopRemove\Exception\ShopRemoveShopNotFoundException;
use Shop\Domain\Service\ShopRemove\Dto\ShopRemoveDto;
use Shop\Domain\Service\ShopRemove\ShopRemoveService;

class ShopRemoveUseCase extends ServiceBase
{
    public function __construct(
        private ShopRemoveService $shopRemoveService,
        private ModuleCommunicationInterface $moduleCommunication,
        private ValidationInterface $validator,
        private ValidateGroupAndUserService $validateGroupAndUserService
    ) {
    }

    public function __invoke(ShopRemoveInputDto $input): ShopRemoveOutputDto
    {
        $this->validation($input);

        try {
            $this->validateGroupAndUserService->__invoke($input->groupId);

            $shopRemovedId = $this->shopRemoveService->__invoke(
                $this->createShopRemoveDto($input->groupId, $input->shopsId)
            );

            return $this->createShopRemoveOutputDto($shopRemovedId);
        } catch (DBNotFoundException) {
            throw ShopRemoveShopNotFoundException::fromMessage('Shop not found');
        } catch (ValidateGroupAndUserException) {
            throw ShopRemoveGroupOrUserNotValidException::fromMessage('You have not permissions');
        } catch (Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     * @throws ShopRemoveGroupOrUserNotValidException
     */
    private function validation(ShopRemoveInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @param Identifier[] $shopsId
     */
    private function createShopRemoveDto(Identifier $groupId, array $shopsId): ShopRemoveDto
    {
        return new ShopRemoveDto($shopsId, $groupId);
    }

    /**
     * @param Identifier[] $shopRemovedId
     */
    private function createShopRemoveOutputDto(array $shopRemovedId): ShopRemoveOutputDto
    {
        return new ShopRemoveOutputDto($shopRemovedId);
    }
}
