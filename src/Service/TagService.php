<?php

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
     * @param \App\Repository\TagRepository $tagRepository
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
            $choices[$tag->getValue()] = $tag->getId();
        }

        return $choices;
    }

    /**
     * Add tag if it does not already exist.
     *
     * @param \App\Entity\Item $item
     * @param $field
     * @param $value
     */
    public function addTag(Item $item, $field, $value) {
        $classname = get_class($item);
        $tags = $this->tagRepository->findBy([
            'class' => $classname,
            'field' => $field,
            'value' => $value,
        ]);

        if (count($tags) === 0) {
            $tag = new Tag();
            $tag->setClass($classname);
            $tag->setField($field);
            $tag->setValue($value);

            $this->entityManager->persist($tag);
            $this->entityManager->flush();
        }

        $this->cleanupTags($classname, $field);
    }

    /**
     * Remove tags that are not related to an entity.
     *
     * @param string $classname
     * @param string $field
     */
    private function cleanupTags(string $classname, string $field) {}
}
