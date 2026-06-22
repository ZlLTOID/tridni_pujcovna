<?php

namespace App\Command;

use App\Entity\Ucitel;
use App\Repository\UcitelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:init-database', description: 'Vytvoří výchozího administrátora, pokud databáze je prázdná')]
class InitDatabaseCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UcitelRepository $ucitelRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->ucitelRepository->countAll() > 0) {
            $output->writeln('Databáze už obsahuje uživatele — přeskakuji inicializaci.');

            return Command::SUCCESS;
        }

        $admin = new Ucitel();
        $admin->setUsername('admin');
        $admin->setJmeno('Admin');
        $admin->setPrijmeni('Systému');
        $admin->setRole('admin');
        $admin->setPasswordHash($this->passwordHasher->hashPassword($admin, 'admin123'));

        $this->em->persist($admin);
        $this->em->flush();

        $output->writeln('Vytvořen výchozí administrátor: admin / admin123');

        return Command::SUCCESS;
    }
}
