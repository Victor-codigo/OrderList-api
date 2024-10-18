<?php

declare(strict_types=1);

namespace Share\Application\ShareListOrdersGetData;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Exception\DomainInternalErrorException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\ServiceBase;
use Common\Domain\Validation\Exception\ValueObjectValidationException;
use Common\Domain\Validation\ValidationInterface;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;
use ListOrders\Domain\Service\ListOrdersGetData\ListOrdersGetDataService;
use Share\Application\ShareListOrdersCreate\Exception\ShareCreateListOrdersNotFoundException;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataInputDto;
use Share\Application\ShareListOrdersGetData\Dto\ShareListOrdersGetDataOutputDto;
use Share\Domain\Model\Share;
use Share\Domain\Service\ShareGetResources\Dto\ShareGetResourcesDto;
use Share\Domain\Service\ShareGetResources\ShareGetResourcesService;

class ShareListOrdersGetDataUseCase extends ServiceBase
{
    public function __construct(
        private ShareGetResourcesService $shareGetResourcesService,
        private ValidationInterface $validator,
        private ListOrdersGetDataService $listOrdersGetData,
    ) {
    }

    /**
     * @throws ValueObjectValidationException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ShareListOrdersGetDataInputDto $input): ShareListOrdersGetDataOutputDto
    {
        $this->validation($input);

        try {
            $listOrdersShared = $this->getListOrdersShared($input->listOrdersId);
            $listOrdersSharedData = $this->getListOrdersSharedData($listOrdersShared);

            return $this->createShareListOrdersGeDataOutputDto($listOrdersSharedData);
        } catch (DBNotFoundException $e) {
            throw ShareCreateListOrdersNotFoundException::fromMessage('List orders not found');
        } catch (\Exception) {
            throw DomainInternalErrorException::fromMessage('An error has been occurred');
        }
    }

    /**
     * @throws ValueObjectValidationException
     */
    private function validation(ShareListOrdersGetDataInputDto $input): void
    {
        $errorList = $input->validate($this->validator);

        if (!empty($errorList)) {
            throw ValueObjectValidationException::fromArray('Error', $errorList);
        }
    }

    /**
     * @throws DBNotFoundException
     */
    private function getListOrdersShared(Identifier $listOrdersIdShared): Share
    {
        $sharedListsOrders = $this->shareGetResourcesService->__invoke(
            $this->createShareListOrdersCreateDto($listOrdersIdShared)
        );

        if (empty($sharedListsOrders)) {
            throw new DBNotFoundException();
        }

        return $sharedListsOrders[0];
    }

    /**
     * @return array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * }
     *
     * @throws DBNotFoundException
     */
    private function getListOrdersSharedData(Share $listOrdersShared): array
    {
        $listOrdersData = $this->listOrdersGetData->__invoke(
            $this->createListOrdersGetDataDto($listOrdersShared)
        );

        if (empty($listOrdersData)) {
            throw new DBNotFoundException();
        }

        return $listOrdersData[0];
    }

    private function createShareListOrdersCreateDto(Identifier $listOrdersId): ShareGetResourcesDto
    {
        return new ShareGetResourcesDto([$listOrdersId]);
    }

    private function createListOrdersGetDataDto(Share $sharedRecourse): ListOrdersGetDataDto
    {
        return new ListOrdersGetDataDto(
            $sharedRecourse->getGroupId(),
            [$sharedRecourse->getListOrdersId()],
            true,
            null,
            null,
            ValueObjectFactory::createPaginatorPage(1),
            ValueObjectFactory::createPaginatorPageItems(1)
        );
    }

    /**
     * @param array{
     *  id: string|null,
     *  user_id: string|null,
     *  group_id: string|null,
     *  name: string|null,
     *  description: string|null,
     *  date_to_buy: string|null,
     *  created_on: string
     * } $sharedListData
     */
    private function createShareListOrdersGeDataOutputDto(array $sharedListData): ShareListOrdersGetDataOutputDto
    {
        return new ShareListOrdersGetDataOutputDto($sharedListData);
    }
}
