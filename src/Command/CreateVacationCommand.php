<?php

namespace App\Command;

use App\DTO\VacationDTO;
use App\Service\UserManager;
use App\Service\VacationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:vacation:create')]
class CreateVacationCommand extends Command
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

        $vacation = new VacationDTO('2024-04-30', '2024-05-08', 'Testuojamas');

        $vacation = $this->manager->requestVacation($user, $vacation);

        $output->writeln('Vacation created successfully!');

        return Command::SUCCESS;
    }

    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     $user = $this->userManager->getUserByEmail('karzab@admin.com');

    //     $reserve = new ReservedDayDTO('2024-04-24', '2024-04-26', $user);

    //     $vacation = $this->reservedDayManager->reserveDays($reserve);

    //     if ($vacation !== null) {
    //         $output->writeln('Vacation created successfully!');
    //     } else {
    //         $output->writeln('Error: Failed to create vacation');
    //     }

    //     return Command::SUCCESS;
    // }
}
