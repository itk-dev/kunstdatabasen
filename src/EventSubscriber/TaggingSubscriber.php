<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\EventSubscriber;

use App\Entity\Item;
use App\Service\TagService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Class TaggingSubscriber.
 */
class TaggingSubscriber implements EventSubscriber
{
    private $tagService;

    /**
     * TaggingSubscriber constructor.
     *
     * @param \App\Service\TagService $tagService
     */
    public function __construct(TagService $tagService)
    {
        $this->tagService = $tagService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->changeTags('persist', $args);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->changeTags('update', $args);
    }

    /**
     * Save the new tag.
     *
     * @param string                                         $action
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $args
     */
    private function changeTags(string $action, LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Item) {
            $organization = $entity->getOrganization();
            $building = $entity->getBuilding();
            $type = $entity->getType();
            $address = $entity->getAddress();
            $city = $entity->getCity();
            $room = $entity->getRoom();
            $location = $entity->getLocation();

            null !== $type && $this->tagService->addTag($entity, 'type', $type);
            null !== $organization && $this->tagService->addTag($entity, 'organization', $organization);
            null !== $building && $this->tagService->addTag($entity, 'building', $building);
            null !== $address && $this->tagService->addTag($entity, 'address', $address);
            null !== $city && $this->tagService->addTag($entity, 'city', $city);
            null !== $room && $this->tagService->addTag($entity, 'room', $room);
            null !== $location && $this->tagService->addTag($entity, 'location', $location);
        }
    }
}
