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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CreateUserCommand.
 */
class CreateUserCommand extends Command
{
    /* @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var UserRepository $userRepository */
    private $userRepository;

    protected static $defaultName = 'app:create-user';

    /**
     * CreateUserCommand constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     * @param \Doctrine\ORM\EntityManagerInterface                                  $entityManager
     * @param \App\Repository\UserRepository                                        $userRepository
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
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

        $rolesArray = explode(',', $roles);

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($rolesArray);
        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Success.');

        return 0;
    }
}
