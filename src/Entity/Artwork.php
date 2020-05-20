<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtworkRepository")
 */
class Artwork extends Item
{
    public $itemType = 'artwork';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $artist;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $artSerial;

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

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $width;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $depth;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $diameter;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $weight;

    /**
     * @return string|null
     */
    public function getArtist(): ?string
    {
        return $this->artist;
    }

    /**
     * @param string|null $artist
     *
     * @return $this
     */
    public function setArtist(?string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getArtSerial(): ?string
    {
        return $this->artSerial;
    }

    /**
     * @param string|null $artSerial
     *
     * @return $this
     */
    public function setArtSerial(?string $artSerial): self
    {
        $this->artSerial = $artSerial;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductionYear(): ?int
    {
        return $this->productionYear;
    }

    /**
     * @param int|null $productionYear
     *
     * @return $this
     */
    public function setProductionYear(?int $productionYear): self
    {
        $this->productionYear = $productionYear;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getAssessmentDate(): ?\DateTimeInterface
    {
        return $this->assessmentDate;
    }

    /**
     * @param \DateTimeInterface|null $assessmentDate
     *
     * @return $this
     */
    public function setAssessmentDate(?\DateTimeInterface $assessmentDate): self
    {
        $this->assessmentDate = $assessmentDate;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAssessmentPrice(): ?float
    {
        return $this->assessmentPrice;
    }

    /**
     * @param float|null $assessmentPrice
     *
     * @return $this
     */
    public function setAssessmentPrice(?float $assessmentPrice): self
    {
        $this->assessmentPrice = $assessmentPrice;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return float|null
     */
    public function getWidth(): ?float
    {
        return $this->width;
    }

    /**
     * @param float|null $width
     *
     * @return $this
     */
    public function setWidth(?float $width): self
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getHeight(): ?float
    {
        return $this->height;
    }

    /**
     * @param float|null $height
     *
     * @return $this
     */
    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDepth(): ?float
    {
        return $this->depth;
    }

    /**
     * @param float|null $depth
     *
     * @return $this
     */
    public function setDepth(?float $depth): self
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getDiameter(): ?float
    {
        return $this->diameter;
    }

    /**
     * @param float|null $diameter
     *
     * @return $this
     */
    public function setDiameter(?float $diameter): self
    {
        $this->diameter = $diameter;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getWeight(): ?float
    {
        return $this->weight;
    }

    /**
     * @param float|null $weight
     *
     * @return $this
     */
    public function setWeight(?float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }
}
