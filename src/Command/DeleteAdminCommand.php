<?php

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

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
            ->addArgument('identifier', InputArgument::REQUIRED, 'Existing email or username of the admin user.')
            ->setDescription('Deletes admin user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifier = $input->getArgument('identifier');

        try {
            $this->userManager->deleteAdmin($identifier);
            $output->writeln('Admin user ' . $identifier . ' deleted successfully!');
            return Command::SUCCESS;
        } catch (UserNotFoundException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
