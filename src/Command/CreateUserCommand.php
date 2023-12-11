<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class CreateUserCommand.
 */
#[AsCommand(
    name: 'app:create-user',
    description: 'Create user',
)]
class CreateUserCommand extends Command
{
    /**
     * CreateUserCommand constructor.
     */
    public function __construct(
        readonly private UserPasswordHasherInterface $passwordHasher,
        readonly private EntityManagerInterface $entityManager,
        readonly private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Create a user')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email?');

        $password = $io->askHidden('Password?');

        $roles = $io->ask('Roles (comma separated list)?');

        if (empty($email) || empty($password)) {
            $io->error('Email and password cannot be null');

            return 1;
        }

        $users = $this->userRepository->findBy(['email' => $email]);

        if (\count($users) > 0) {
            $io->error('User already exists');

            return 1;
        }

        $rolesArray = explode(',', (string) $roles);

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($rolesArray);
        $encodedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Success.');

        return 0;
    }
}
