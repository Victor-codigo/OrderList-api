<?php

declare(strict_types=1);

namespace User\Adapter\Command\UsersRemoveActivationExpired;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Domain\Port\Repository\UserRepositoryInterface;

#[AsCommand(
    'app:user:remove-not-active-expired',
    'Removes all users that are not active, and which time for activation has expired',
    ['a:u:rna'],
    false
)]
class UsersRemoveActivationExpiredCommand extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private int $userActivationTimeExpiration
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void
    {
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $users = $this->userRepository->findUsersTimeActivationExpiredOrFail($this->userActivationTimeExpiration);
            $users->setPagination();
            $usersNumPages = $users->getPagesTotal();

            for ($i = 1; $i <= $usersNumPages; ++$i) {
                $this->userRepository->remove(iterator_to_array($users));
            }

            $output->writeln('Users not active and expired removed');

            return Command::SUCCESS;
        } catch (DBNotFoundException) {
            $output->writeln('No users to remove');

            return Command::SUCCESS;
        } catch (\Throwable) {
            return Command::FAILURE;
        }
    }
}
