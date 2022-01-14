<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\EventSubscriber;

use App\Entity\Artwork;
use App\Entity\Item;
use App\Service\TagService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class TaggingSubscriber.
 */
class TaggingSubscriber implements EventSubscriber
{
    private $tagService;
    private $dispatcher;

    /**
     * TaggingSubscriber constructor.
     *
     * @param \App\Service\TagService                                     $tagService
     * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(TagService $tagService, EventDispatcherInterface $dispatcher)
    {
        $this->tagService = $tagService;
        $this->dispatcher = $dispatcher;
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
        if ($args->getObject() instanceof Item) {
            $this->dispatcher->addListener(
                KernelEvents::TERMINATE,
                function (TerminateEvent $event) use ($args) {
                    $this->changeTags('persist', $args);
                }
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        if ($args->getObject() instanceof Item) {
            $this->dispatcher->addListener(
                KernelEvents::TERMINATE,
                function (TerminateEvent $event) use ($args) {
                    $this->changeTags('update', $args);
                }
            );
        }
    }

    /**
     * Save the new tag.
     *
     * @param string             $action
     * @param LifecycleEventArgs $args
     */
    private function changeTags(string $action, LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Item) {
            $organization = $entity->getOrganization();
            $building = $entity->getBuilding();
            $department = $entity->getDepartment();
            $type = $entity->getType();
            $address = $entity->getAddress();
            $city = $entity->getCity();
            $room = $entity->getRoom();
            $location = $entity->getLocation();
            $status = $entity->getStatus();

            $artistGender = null;
            if ($entity instanceof Artwork) {
                $artistGender = $entity->getArtistGender();
            }

            $changeSet = [];

            if ($args instanceof PreUpdateEventArgs) {
                $changeSet = $args->getEntityChangeSet();
            }

            null !== $type && $this->tagService->addTag($entity, 'type', $type, $changeSet['type'] ?? []);
            null !== $organization && $this->tagService->addTag($entity, 'organization', $organization, $changeSet['organization'] ?? []);
            null !== $building && $this->tagService->addTag($entity, 'building', $building, $changeSet['building'] ?? []);
            null !== $department && $this->tagService->addTag($entity, 'department', $department, $changeSet['department'] ?? []);
            null !== $address && $this->tagService->addTag($entity, 'address', $address, $changeSet['address'] ?? []);
            null !== $city && $this->tagService->addTag($entity, 'city', $city, $changeSet['city'] ?? []);
            null !== $room && $this->tagService->addTag($entity, 'room', $room, $changeSet['room'] ?? []);
            null !== $status && $this->tagService->addTag($entity, 'status', $status, $changeSet['status'] ?? []);
            null !== $location && $this->tagService->addTag($entity, 'location', $location, $changeSet['location'] ?? []);
            null !== $artistGender && $this->tagService->addTag($entity, 'artistGender', $artistGender, $changeSet['artistGender'] ?? []);
        }
    }
}
