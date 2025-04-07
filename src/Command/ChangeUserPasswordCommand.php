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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class ChangeUserPasswordCommand.
 */
#[AsCommand(
    name: 'app:change-user-password',
    description: 'Change user password',
)]
class ChangeUserPasswordCommand extends Command
{
    /**
     * ChangeUserRolesCommand constructor.
     */
    public function __construct(
        readonly private UserPasswordHasherInterface $passwordHasher,
        readonly private EntityManagerInterface $entityManager,
        readonly private UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
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

        $password = $io->ask('Password?');

        if (empty($email) || empty($password)) {
            $io->error('Email and password cannot be null');

            return 1;
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            $io->error('User does not exist');

            return 1;
        }

        $encodedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encodedPassword);
        $this->entityManager->flush();

        $io->success('Success.');

        return 0;
    }
}
