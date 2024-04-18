<?php

namespace App\Command;

use App\DTO\VacationDTO;
use App\Service\UserManager;
use App\Service\VacationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vacation:confirm')]
class ConfirmVacationCommand extends Command
{
    public function __construct(
        private VacationManager $manager,
        private UserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->userManager->getUserByEmail('karzab@admin.com');

        $vacation = new VacationDTO('', '', '', $user, '');

        // $vacation = $this->manager->requestVacation($user, $vacation);
        $vacation = $this->manager->confirmVacationRequest('1', $vacation);

        $output->writeln('Vacation created successfully!');

        return Command::SUCCESS;
    }

    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     $user = $this->userManager->getUserByEmail('karzab@admin.com');

    //     $vacation = new VacationDTO('', '', '', $user, 'Svarbus event');

    //     // $vacation = $this->manager->requestVacation($user, $vacation);
    //     $vacation = $this->manager->rejectVacationRequest('2', $vacation);

    //     if ($vacation !== null) {
    //         $output->writeln('Vacation created successfully!');
    //     } else {
    //         $output->writeln('Error: Failed to create vacation');
    //     }

    //     return Command::SUCCESS;
    // }
}
