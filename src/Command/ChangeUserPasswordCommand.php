<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class ChangeUserPasswordCommand.
 */
class ChangeUserPasswordCommand extends Command
{
    /* @var UserPasswordEncoderInterface $passwordEncoder */
    private $passwordEncoder;
    /* @var EntityManagerInterface $entityManager */
    private $entityManager;
    /* @var UserRepository $userRepository */
    private $userRepository;

    protected static $defaultName = 'app:change-user-password';

    /**
     * ChangeUserRolesCommand constructor.
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

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);
        $this->entityManager->flush();

        $io->success('Success.');

        return 0;
    }
}
