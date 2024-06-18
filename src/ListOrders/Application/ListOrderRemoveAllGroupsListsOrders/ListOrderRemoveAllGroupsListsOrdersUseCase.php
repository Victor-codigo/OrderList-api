<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrderRemoveAllGroupsListsOrders;

use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Application\ListOrderRemoveAllGroupsListsOrders\Dto\ListOrderRemoveAllGroupsListsOrdersInputDto;
use ListOrders\Application\ListOrderRemoveAllGroupsListsOrders\Dto\ListOrderRemoveAllGroupsListsOrdersOutputDto;
use ListOrders\Application\ListOrderRemoveAllGroupsListsOrders\Exception\ListOrderRemoveAllGroupsListsOrdersSystemKeyException;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListOrdersOutputDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\ListOrdersRemoveAllGroupsListsOrdersService;

class ListOrderRemoveAllGroupsListsOrdersUseCase extends ServiceBase
{
    public function __construct(
        private ListOrdersRemoveAllGroupsListsOrdersService $listOrdersRemoveAllGroupsListsOrdersService,
        private ValidationInterface $validator,
        private string $systemKey
    ) {
    }

    /**
     * @throws listOrdersRemoveListOrdersNotFoundException
     * @throws listOrdersRemoveGroupAndUserValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ListOrderRemoveAllGroupsListsOrdersInputDto $input): ListOrderRemoveAllGroupsListsOrdersOutputDto
    {
        $this->validation($input);

        try {
            $listsOrdersIdRemovedAndUserIdChanged = $this->listOrdersRemoveAllGroupsListsOrdersService->__invoke(
                $this->createListOrdersRemoveAllGroupsListsOrdersDto($input)
            );

            return $this->createListOrdersRemoveAllGroupsListOrdersOutputDto($listsOrdersIdRemovedAndUserIdChanged);
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ListOrderRemoveAllGroupsListsOrdersInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }

        if ($input->systemKey !== $this->systemKey) {
            throw ListOrderRemoveAllGroupsListsOrdersSystemKeyException::fromMessage('Wrong system key');
        }
    }

    private function createListOrdersRemoveAllGroupsListsOrdersDto(ListOrderRemoveAllGroupsListsOrdersInputDto $input): ListOrdersRemoveAllGroupsListsOrdersDto
    {
        return new ListOrdersRemoveAllGroupsListsOrdersDto($input->groupsIdToRemove, $input->groupsIdToChangeUserId, $input->userIdToSet);
    }

    private function createListOrdersRemoveAllGroupsListOrdersOutputDto(ListOrdersRemoveAllGroupsListOrdersOutputDto $listOrdersIdRemovedAndUserIdChanged): ListOrderRemoveAllGroupsListsOrdersOutputDto
    {
        return new ListOrderRemoveAllGroupsListsOrdersOutputDto(
            $listOrdersIdRemovedAndUserIdChanged->listOrdersIdRemoved,
            $listOrdersIdRemovedAndUserIdChanged->listOrdersIdChangedUserId
        );
    }
}
