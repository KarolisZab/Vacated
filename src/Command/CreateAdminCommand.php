<?php

namespace App\Command;

use App\Service\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:admin:create')]
class CreateAdminCommand extends Command
{
    public function __construct(private UserManager $userManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email address.')
            ->addArgument('password', InputArgument::REQUIRED, 'Plain password.')
            ->setDescription('Creates a new Admin user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $adminUser = $this->userManager->createAdmin($email, $password);

        if ($adminUser !== null) {
            $output->writeln('Admin user ' . $email . ' created successfully!');
        } else {
            $output->writeln('Error: Failed to create admin user.');
        }

        return Command::SUCCESS;
    }
}
