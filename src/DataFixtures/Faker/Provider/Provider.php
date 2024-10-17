<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\DataFixtures\Faker\Provider;

use Faker\Generator;
use Faker\Provider\Base;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Twig\Environment as Twig;
use Twig\Error\Error as TwigError;

class Provider extends Base
{
    public function __construct(
        Generator $generator,
        private readonly Filesystem $filesystem,
        private readonly MimeTypeGuesserInterface $mimeTypeGuesser,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly Twig $twig,
        private readonly array $config,
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

    /**
     * Simulate file upload using VichUploader.
     *
     * @param string $path the file path relative to the fixtures directory
     */
    public function uploadFile(string $path)
    {
        // Process Twig expressions in path.
        try {
            $path = $this->twig->createTemplate($path)->render();
        } catch (TwigError) {
        }

        $sourcePath = $this->config['project_dir'].'/fixtures/'.$path;
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException(\sprintf('File source path %s does not exist', $sourcePath));
        }

        // The uploaded file will be deleted, so we create a copy of the input file.
        $tmpPath = $this->filesystem->tempnam(sys_get_temp_dir(), 'upload');
        $this->filesystem->copy($sourcePath, $tmpPath, true);

        return new UploadedFile(
            $tmpPath,
            basename($sourcePath),
            null,
            null,
            true
        );
    }
}
