<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtworkRepository")
 */
class Artwork
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $artist;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $artSerial;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $purchasePrice;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $productionYear;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $assessmentDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $assessmentPrice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getArtSerial(): ?string
    {
        return $this->artSerial;
    }

    public function setArtSerial(string $artSerial): self
    {
        $this->artSerial = $artSerial;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(?float $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getProductionYear(): ?int
    {
        return $this->productionYear;
    }

    public function setProductionYear(?int $productionYear): self
    {
        $this->productionYear = $productionYear;

        return $this;
    }

    public function getAssessmentDate(): ?\DateTimeInterface
    {
        return $this->assessmentDate;
    }

    public function setAssessmentDate(?\DateTimeInterface $assessmentDate): self
    {
        $this->assessmentDate = $assessmentDate;

        return $this;
    }

    public function getAssessmentPrice(): ?float
    {
        return $this->assessmentPrice;
    }

    public function setAssessmentPrice(?float $assessmentPrice): self
    {
        $this->assessmentPrice = $assessmentPrice;

        return $this;
    }
}
