<?php

namespace App\Command;

use App\Service\ItemService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportImagesCommand extends Command
{
    protected static $defaultName = 'app:import-images';
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
            ->setDescription('Import images from a folder. Each image should be named by inventoryId.[extension].')
            ->addArgument('folder', InputArgument::REQUIRED, 'Folder of images to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $folder = $input->getArgument('folder');

        if ($folder) {
            $io->note(sprintf('Importing images from: %s', $folder));
        }

        $this->itemService->importFromImages($folder);

        $io->success('Images successfully imported.');

        return 0;
    }
}
