<?php

declare(strict_types=1);

namespace Share\Domain\Service\ShareRecoursesRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareRecoursesRemove\Dto\ShareRecoursesRemoveDto;

class ShareRecoursesRemoveService
{
    public function __construct(
        private ShareRepositoryInterface $shareRepository,
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(ShareRecoursesRemoveDto $input): array
    {
        $sharedRecurses = $this->getSharedRecurse($input->RecursesId);
        $this->shareRepository->remove($sharedRecurses);

        return array_map(
            fn (Share $share): Identifier => $share->getId(),
            $sharedRecurses
        );
    }

    /**
     * @param Identifier[] $recursesId
     *
     * @return Share[]
     *
     * @throws DBNotFoundException
     */
    private function getSharedRecurse(array $recursesId): array
    {
        $sharedRecurses = $this->shareRepository->findSharedRecursesByIdOrFail($recursesId);
        $sharedRecurses->setPagination(1, count($recursesId));

        return iterator_to_array($sharedRecurses);
    }
}
