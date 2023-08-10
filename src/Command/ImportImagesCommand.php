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
 * Class ImportImagesCommand.
 */
#[AsCommand(
    name: 'app:import-images'
)]
class ImportImagesCommand extends Command
{
    /**
     * ImportSpreadsheetCommand constructor.
     */
    public function __construct(
        readonly private ItemService $itemService
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Import images from a folder. Each image should be named by inventoryId.[extension].')
            ->addArgument('folder', InputArgument::REQUIRED, 'Folder of images to import')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $folder = $input->getArgument('folder');

        if ($folder) {
            $io->note(sprintf('Importing images from: %s', $folder));
        }

        $this->itemService->importFromImages($folder);

        $io->success('Images successfully imported.');

        return self::SUCCESS;
    }
}
