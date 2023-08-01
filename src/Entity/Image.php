<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[Vich\Uploadable()]
class Image
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'images')]
    private $item;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="artwork_image", fileNameProperty="imageName", size="imageSize")
     *
     * @var File|null
     */
    #[Vich\UploadableField(mapping: 'artwork_image', fileNameProperty: 'imageName', size: 'imageSize')]
    private $imageFile;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string')]
    private $imageName;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer')]
    private $imageSize;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $primaryImage;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getImageName();
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     *
     * @throws \Exception
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\File|null
     */
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @param string|null $imageName
     */
    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    /**
     * @return string|null
     */
    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    /**
     * @param int|null $imageSize
     */
    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    /**
     * @return int|null
     */
    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Item|null
     */
    public function getItem(): ?Item
    {
        return $this->item;
    }

    /**
     * @param \App\Entity\Item|null $item
     *
     * @return $this
     */
    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPrimaryImage(): ?bool
    {
        return $this->primaryImage;
    }

    /**
     * @param bool|null $primaryImage
     *
     * @return $this
     */
    public function setPrimaryImage(?bool $primaryImage): self
    {
        $this->primaryImage = $primaryImage;

        return $this;
    }
}
