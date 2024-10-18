<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareGetResources;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareGetResources\Dto\ShareGetResourcesDto;

class ShareGetResourcesService
{
    public function __construct(
        private ShareRepositoryInterface $shareRepository,
    ) {
    }

    /**
     * @return Share[]
     */
    public function __invoke(ShareGetResourcesDto $input): array
    {
        return $this->getListOrdersShared($input->resourcesId);
    }

    /**
     * @param Identifier[] $sharedListId
     *
     * @return Share[]
     */
    private function getListOrdersShared(array $sharedListId): array
    {
        try {
            $sharedListOrdersPaginator = $this->shareRepository->findSharedRecursesByIdOrFail($sharedListId);
            $sharedListOrdersPaginator->setPagination(1, count($sharedListId));

            return iterator_to_array($sharedListOrdersPaginator->getIterator());
        } catch (DBNotFoundException $e) {
            return [];
        }
    }
}
