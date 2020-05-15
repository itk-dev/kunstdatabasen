<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Artwork;
use App\Entity\Item;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ItemService.
 */
class ItemService
{
    protected $uploaderHelper;
    protected $router;

    /**
     * ItemService constructor.
     *
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper      $uploaderHelper
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     */
    public function __construct(UploaderHelper $uploaderHelper, UrlGeneratorInterface $router)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->router = $router;
    }

    /**
     * Create render object for item.
     *
     * @param \App\Entity\Item $item
     *
     * @return object
     */
    public function itemToRenderObject(Item $item)
    {
        $path = '';
        if (\count($item->getImages()) > 0) {
            $path = $this->uploaderHelper->asset($item->getImages()[0], 'imageFile');
        }

        $renderObject = (object) [
            'id' => $item->getId(),
            'link' => $this->router->generate(
                'artwork_show',
                [
                    'id' => $item->getId(),
                ]
            ),
            'img' => $path,
            'title' => $item->getName(),
            'type' => $item->getType(),
            'building' => $item->getBuilding(),
            'geo' => $item->getGeo(),
            'comment' => $item->getComment(),
            'department' => $item->getOrganization(),
            'status' => $item->getStatus(),
            'linkEdit' => $this->router->generate('artwork_edit', ['id' => $item->getId()]),
        ];

        if ($item instanceof Artwork) {
            $renderObject->artNo = $item->getArtSerial();
            $renderObject->artist = $item->getArtist();
            $renderObject->dimensions = $this->getDimensions($item);
            $renderObject->price = $item->getPurchasePrice();
            $renderObject->productionYear = $item->getProductionYear();
            $renderObject->estimatedValue = $item->getAssessmentPrice();
            $renderObject->estimatedValueDate = $item->getAssessmentDate()->format('d/m Y');
        }

        return $renderObject;
    }

    /**
     * Get dimensions.
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return string
     */
    private function getDimensions(Artwork $artwork)
    {
        $width = $artwork->getWidth();
        $height = $artwork->getHeight();

        // @TODO: Include depth, diameter and weight in string.

        return sprintf('%d X %d', $width, $height);
    }
}
