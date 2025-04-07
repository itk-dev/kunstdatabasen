<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Service\ItemService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ImportSpreadsheetCommand.
 */
#[AsCommand(
    name: 'app:import-spreadsheet'
)]
class ImportSpreadsheetCommand extends Command
{
    /**
     * ImportSpreadsheetCommand constructor.
     */
    public function __construct(
        readonly private ItemService $itemService,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Import items from a spreadsheet')
            ->addArgument('file', InputArgument::REQUIRED, 'File to import')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');

        if ($file) {
            $io->note(\sprintf('Importing: %s', $file));
        }

        $this->itemService->importFromSpreadsheet($file);

        $io->success('Items successfully imported.');

        return self::SUCCESS;
    }
}
