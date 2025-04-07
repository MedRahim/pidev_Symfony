<?php

namespace App\Command;

use Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand;
use Doctrine\ORM\Tools\Console\Command\ImportMappingCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:generate:entities')]
class GenerateEntitiesCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Génération des entités à partir de la base city_transport...</info>');

        // Exécuter la commande Doctrine pour importer les entités depuis la base de données
        shell_exec('symfony console doctrine:mapping:import "App\Entity" annotation --path=src/Entity');
        shell_exec('symfony console doctrine:mapping:convert annotation --force --from-database --namespace="App\Entity\\" src/Entity');
        shell_exec('symfony console make:entity --regenerate');

        $output->writeln('<info>Les entités ont été générées avec succès !</info>');

        return Command::SUCCESS;
    }
}
