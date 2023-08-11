<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ChangeUserRolesCommand.
 */
#[AsCommand(
    name: 'app:change-user-roles',
    description: 'Change user password',
)]
class ChangeUserRolesCommand extends Command
{
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var UserRepository $userRepository */
    private $userRepository;

    /**
     * ChangeUserRolesCommand constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Repository\UserRepository       $userRepository
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Change a user\'s roles')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email?');

        $roles = $io->ask('Roles (comma separated list)?');

        if (empty($email)) {
            $io->error('Email cannot be null');

            return 1;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            $io->error('User does not exist');

            return 1;
        }

        $rolesArray = explode(',', $roles);

        $user->setRoles($rolesArray);
        $this->entityManager->flush();

        $io->success('Success.');

        return 0;
    }
}
