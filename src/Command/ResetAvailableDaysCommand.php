<?php

namespace App\Command;

use App\DTO\UserDTO;
use App\Service\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:user:reset-available-days')]
class ResetAvailableDaysCommand extends Command
{
    public function __construct(
        private UserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Reset available vacation days for all users to default value');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->userManager->getAllUsers();

        foreach ($users as $user) {
            $userDTO = new UserDTO(
                $user->getEmail(),
                $user->getFirstName(),
                $user->getLastName(),
                $user->getPhoneNumber(),
                20,
                null,
                $user->getTags()->toArray()
            );

            $this->userManager->updateUser($user->getId(), $userDTO);
        }

        $output->writeln('Available vacation days for all users have been reset to default value (20)');

        return Command::SUCCESS;
    }
}
