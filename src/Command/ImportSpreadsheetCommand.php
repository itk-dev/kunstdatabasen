<?php

namespace App\Command;

use App\Service\ItemService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportSpreadsheetCommand extends Command
{
    protected static $defaultName = 'app:import-spreadsheet';
    private $itemService;

    /**
     * ImportSpreadsheetCommand constructor.
     *
     * @param \App\Service\ItemService $itemService
     */
    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Import items from a spreadsheet')
            ->addArgument('file', InputArgument::REQUIRED, 'File to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if ($file) {
            $io->note(sprintf('Importing: %s', $file));
        }

        $this->itemService->importFromSpreadsheet($file);

        $io->success('Items successfully imported.');

        return 0;
    }
}
