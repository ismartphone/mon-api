<?php

namespace App\Command;

use App\Service\MovieImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-movies',
    description: 'Imports movies, categories and actors from the sample_mflix movies.json dataset',
)]
final class ImportMoviesCommand extends Command
{
    public function __construct(private readonly MovieImporter $movieImporter)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Path to the movies.json file', 'data/movies.json')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Maximum number of movies to import');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = (string) $input->getArgument('file');
        $limit = null !== $input->getOption('limit') ? (int) $input->getOption('limit') : null;

        if (!is_file($filePath)) {
            $io->error(\sprintf('File "%s" not found. Download it first (see README.md).', $filePath));

            return Command::FAILURE;
        }

        ini_set('memory_limit', '2048M');

        $io->title('Importing movies');
        $importedCount = $this->movieImporter->import(
            $filePath,
            $limit,
            static fn (int $count) => $io->writeln(\sprintf('%d movies imported...', $count)),
        );

        $io->success(\sprintf('%d movies imported.', $importedCount));

        return Command::SUCCESS;
    }
}
