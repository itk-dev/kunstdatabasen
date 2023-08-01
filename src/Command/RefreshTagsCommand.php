<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Service\TagService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RefreshTagsCommand.
 */
#[AsCommand(
    name: 'app:refresh-tags',
    description: 'Refresh tags',
)]
class RefreshTagsCommand extends Command
{
    private $tagService;

    /**
     * ImportSpreadsheetCommand constructor.
     *
     * @param TagService $tagService
     *                               The tag service
     */
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Refreshes tags.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->tagService->refreshTags();

        $io->success('Tags successfully imported.');

        return 0;
    }
}
