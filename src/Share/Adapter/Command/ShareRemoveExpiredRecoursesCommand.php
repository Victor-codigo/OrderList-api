<?php

declare(strict_types=1);

namespace Share\Adapter\Command;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Share\Domain\Model\Share;
use Share\Domain\Port\Repository\ShareRepositoryInterface;
use Share\Domain\Service\ShareRecoursesRemove\Dto\ShareRecoursesRemoveDto;
use Share\Domain\Service\ShareRecoursesRemove\ShareRecoursesRemoveService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    'app:share:remove-expired',
    'Remove a share Recurse',
)]
class ShareRemoveExpiredRecoursesCommand extends Command
{
    public function __construct(
        private ShareRepositoryInterface $shareRepositoryInterface,
        private ShareRecoursesRemoveService $shareRecurseRemoveService,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sharedRecoursesExpired = $this->getRecoursesExpired();

        $sharedRecoursesExpiredId = array_map(
            fn (Share $share): Identifier => $share->getId(),
            $sharedRecoursesExpired
        );

        try {
            $input = new ShareRecoursesRemoveDto($sharedRecoursesExpiredId);
            $this->shareRecurseRemoveService->__invoke($input);

            $output->write('Shared recourses removed successfully');

            return Command::SUCCESS;
        } catch (DBNotFoundException $e) {
            $output->write('No shared recourses to remove');

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $output->write('An error has been occurred');

            return Command::FAILURE;
        }
    }

    /**
     * @return Share[]
     */
    private function getRecoursesExpired(): array
    {
        try {
            $sharedRecursesExpiredPaginator = $this->shareRepositoryInterface->findSharedRecursesExpiredOrFail();

            return iterator_to_array($sharedRecursesExpiredPaginator);
        } catch (\Throwable $th) {
            return [];
        }
    }
}
