<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Item;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class TagService.
 */
class TagService
{
    private $tagRepository;
    private $entityManager;

    /**
     * TagService constructor.
     *
     * @param \App\Repository\TagRepository        $tagRepository
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(TagRepository $tagRepository, EntityManagerInterface $entityManager)
    {
        $this->tagRepository = $tagRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Get choices for a given classname and field.
     *
     * @param string $classname
     * @param string $field
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
     * Add tag if it does not already exist.
     *
     * @param \App\Entity\Item $item
     *                                    The item that has added the tag
     * @param string           $field
     *                                    The field of the tag
     * @param mixed            $value
     *                                    The field value
     * @param array            $changeSet
     *                                    The change set for the field, if it exists
     */
    public function addTag(Item $item, string $field, $value, array $changeSet)
    {
        $classname = \get_class($item);
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
}
