<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Artwork;
use App\Entity\Item;
use App\Entity\Tag;
use App\Repository\ItemRepository;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagService.
 */
class TagService
{
    /**
     * TagService constructor.
     */
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ItemRepository $itemRepository,
    ) {
    }

    /**
     * Get choices for a given classname and field.
     *
     * @return array
     */
    public function getChoices(string $classname, string $field)
    {
        $tags = $this->tagRepository->getByClassname($classname, $field);

        $choices = [];

        /* @var Tag $tag */
        foreach ($tags as $tag) {
            $choices[$tag->getValue()] = $tag->getValue();
        }

        return $choices;
    }

    /**
     * Refresh tags.
     */
    public function refreshTags()
    {
        $items = $this->itemRepository->findAll();

        echo \count($items)." items.\n";

        foreach ($items as $item) {
            echo '.';

            $organization = $item->getOrganization();
            $building = $item->getBuilding();
            $department = $item->getDepartment();
            $type = $item->getType();
            $address = $item->getAddress();
            $city = $item->getCity();
            $room = $item->getRoom();
            $location = $item->getLocation();
            $status = $item->getStatus();

            $artistGender = null;
            if ($item instanceof Artwork) {
                $artistGender = $item->getArtistGender();
            }

            null !== $type && $this->addTagWithoutCleanup($item, 'type', $type);
            null !== $organization && $this->addTagWithoutCleanup($item, 'organization', $organization);
            null !== $building && $this->addTagWithoutCleanup($item, 'building', $building);
            null !== $department && $this->addTagWithoutCleanup($item, 'department', $department);
            null !== $address && $this->addTagWithoutCleanup($item, 'address', $address);
            null !== $city && $this->addTagWithoutCleanup($item, 'city', $city);
            null !== $room && $this->addTagWithoutCleanup($item, 'room', $room);
            null !== $status && $this->addTagWithoutCleanup($item, 'status', $status);
            null !== $location && $this->addTagWithoutCleanup($item, 'location', $location);
            null !== $artistGender && $this->addTagWithoutCleanup($item, 'artistGender', $artistGender);
        }

        $this->entityManager->flush();
    }

    /**
     * Add tag if it does not already exist.
     *
     * @param Item   $item
     *                          The item that has added the tag
     * @param string $field
     *                          The field of the tag
     * @param mixed  $value
     *                          The field value
     * @param array  $changeSet
     *                          The change set for the field, if it exists
     */
    public function addTag(Item $item, string $field, mixed $value, array $changeSet)
    {
        $classname = $item::class;
        $tag = $this->tagRepository->findOneBy([
            'class' => $classname,
            'field' => $field,
            'value' => $value,
        ]);

        // If the tag does not exist, add it.
        if (null === $tag) {
            $tag = new Tag();
            $tag->setClass($classname);
            $tag->setField($field);
            $tag->setValue($value);

            $this->entityManager->persist($tag);
        }

        // Remove old tag if it is not in use anymore.
        if (!empty($changeSet)) {
            $oldValue = $changeSet[0];

            $repository = $this->entityManager->getRepository($classname);
            $itemsWithTag = $repository->findBy([
                $field => $oldValue,
            ]);

            // Remove tag if the only item that has the tag set, is the current that is changing
            // to not using the tag anymore.
            if (0 === \count($itemsWithTag)) {
                $oldTag = $this->tagRepository->findOneBy([
                    'class' => $classname,
                    'field' => $field,
                    'value' => $oldValue,
                ]);

                if (null !== $oldTag) {
                    $this->entityManager->remove($oldTag);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Add tag without removing old tags.
     *
     * @param $value
     */
    private function addTagWithoutCleanup(Item $item, string $field, $value)
    {
        $classname = $item::class;
        $tag = $this->tagRepository->findOneBy([
            'class' => $classname,
            'field' => $field,
            'value' => $value,
        ]);

        // If the tag does not exist, add it.
        if (null === $tag) {
            $tag = new Tag();
            $tag->setClass($classname);
            $tag->setField($field);
            $tag->setValue($value);

            $this->entityManager->persist($tag);
        }
    }
}
