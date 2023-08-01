<?php

namespace App\DataFixtures\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class Provider extends Base
{
    public function __construct(
        Generator $generator,
        readonly private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct($generator);
    }

    /**
     * Generate user password.
     *
     * Usage:
     *   App\Entity\User:
     *     password: '<password(@self, "apassword")>'
     *
     * @return string
     */
    public function password(PasswordAuthenticatedUserInterface $user, string $plaintextPassword)
    {
        return $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
    }
}
