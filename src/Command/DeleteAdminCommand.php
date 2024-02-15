<?php

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:admin:delete')]
class DeleteAdminCommand extends Command
{
    public function __construct(private UserManager $userManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Existing admin user email.')
            ->setDescription('Deletes admin user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');

        $deleted = $this->userManager->deleteAdmin($email);

        if ($deleted) {
            $output->writeln('Admin user ' . $email . ' deleted successfully!');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>User not found or deletion failed.</error>');
            return Command::FAILURE;
        }
    }
}
