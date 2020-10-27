<?php

namespace App\Command;

use App\Service\ItemService;
use App\Service\TagService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RefreshTagsCommand extends Command
{
    protected static $defaultName = 'app:refresh-tags';
    private $tagService;

    /**
     * ImportSpreadsheetCommand constructor.
     *
     * @param TagService $tagService
     *   The tag service
     */
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Refreshes tags.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->tagService->refreshTags();

        $io->success('Tags successfully imported.');

        return 0;
    }
}
