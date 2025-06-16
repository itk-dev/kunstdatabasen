<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\ItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
class Item
{
    use BlameableEntity;
    use TimestampableEntity;

    public const ITEM_TYPE = 'item';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Image>|Image[]
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'item', cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $organization = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $building = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $room = null;

    #[ORM\Column(type: Types::STRING, length: 500, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $postalCode = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $publiclyAccessible = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $geo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $department = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $purchasePrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $inventoryId = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $purchasePlace = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $purchaseDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $purchasedBy = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $barcode = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $locationDate = null;

    /**
     * Artwork constructor.
     */
    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription(mixed $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images ?? new ArrayCollection();
    }

    /**
     * @return $this
     */
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setItem($this);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getItem() === $this) {
                $image->setItem(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return $this
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return $this
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    /**
     * @param string|null $organization
     *
     * @return $this
     */
    public function setOrganization(?string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string|null $location
     *
     * @return $this
     */
    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBuilding(): ?string
    {
        return $this->building;
    }

    /**
     * @param string|null $building
     *
     * @return $this
     */
    public function setBuilding(?string $building): self
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRoom(): ?string
    {
        return $this->room;
    }

    /**
     * @param string|null $room
     *
     * @return $this
     */
    public function setRoom(?string $room): self
    {
        $this->room = $room;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return $this
     */
    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    /**
     * @param int|null $postalCode
     *
     * @return $this
     */
    public function setPostalCode(?int $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return $this
     */
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPubliclyAccessible(): ?bool
    {
        return $this->publiclyAccessible;
    }

    /**
     * @param bool|null $publiclyAccessible
     *
     * @return $this
     */
    public function setPubliclyAccessible(?bool $publiclyAccessible): self
    {
        $this->publiclyAccessible = $publiclyAccessible;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getGeo(): ?string
    {
        return $this->geo;
    }

    /**
     * @param string|null $geo
     *
     * @return $this
     */
    public function setGeo(?string $geo): self
    {
        $this->geo = $geo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     *
     * @return $this
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDepartment(): ?string
    {
        return $this->department;
    }

    /**
     * @param string|null $department
     *
     * @return $this
     */
    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemType(): string
    {
        return static::ITEM_TYPE;
    }

    /**
     * @return float|null
     */
    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    /**
     * @param float|null $purchasePrice
     *
     * @return $this
     */
    public function setPurchasePrice(?float $purchasePrice): self
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getInventoryId(): ?int
    {
        return $this->inventoryId;
    }

    /**
     * @param int|null $inventoryId
     *
     * @return $this
     */
    public function setInventoryId(?int $inventoryId): self
    {
        $this->inventoryId = $inventoryId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPurchasePlace(): ?string
    {
        return $this->purchasePlace;
    }

    /**
     * @param string|null $purchasePlace
     *
     * @return $this
     */
    public function setPurchasePlace(?string $purchasePlace): self
    {
        $this->purchasePlace = $purchasePlace;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    /**
     * @param \DateTimeInterface|null $purchaseDate
     *
     * @return $this
     */
    public function setPurchaseDate(?\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPurchasedBy(): ?string
    {
        return $this->purchasedBy;
    }

    /**
     * @param string|null $purchasedBy
     *
     * @return $this
     */
    public function setPurchasedBy(?string $purchasedBy): self
    {
        $this->purchasedBy = $purchasedBy;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBarcode(): ?string
    {
        return $this->barcode;
    }

    /**
     * @param string|null $barcode
     *
     * @return $this
     */
    public function setBarcode(?string $barcode): self
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocationDate(): ?\DateTimeInterface
    {
        return $this->locationDate;
    }

    /**
     * @param string|null $locationDate
     *
     * @return $this
     */
    public function setLocationDate(?\DateTimeInterface $locationDate): self
    {
        $this->locationDate = $locationDate;

        return $this;
    }
}
