<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Artwork;
use App\Entity\Furniture;
use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ItemService.
 */
class ItemService
{
    protected $uploaderHelper;
    protected $router;
    protected $entityManager;

    /**
     * ItemService constructor.
     *
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper      $uploaderHelper
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     * @param \Doctrine\ORM\EntityManagerInterface                       $entityManager
     */
    public function __construct(UploaderHelper $uploaderHelper, UrlGeneratorInterface $router, EntityManagerInterface $entityManager)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->router = $router;
        $this->entityManager = $entityManager;
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
        $imagePaths = [];
        foreach ($item->getImages() as $image) {
            $imagePaths[] = $this->uploaderHelper->asset($image, 'imageFile');
        }

        $renderObject = (object) [
            'id' => $item->getId(),
            'images' => $imagePaths,
            'title' => $item->getName(),
            'type' => $item->getType(),
            'building' => $item->getBuilding(),
            'geo' => $item->getGeo(),
            'description' => $item->getDescription(),
            'comment' => $item->getComment(),
            'department' => $item->getOrganization(),
            'status' => $item->getStatus(),
            'linkEdit' => $this->router->generate('item_edit', ['id' => $item->getId()]),
        ];

        if ($item instanceof Artwork) {
            $renderObject->artNo = $item->getArtSerial();
            $renderObject->artist = $item->getArtist();
            $renderObject->dimensions = $this->getDimensions($item);
            $renderObject->price = $item->getPurchasePrice();
            $renderObject->productionYear = $item->getProductionYear();
            $renderObject->estimatedValue = $item->getAssessmentPrice();
            $renderObject->estimatedValueDate = $item->getAssessmentDate() ? $item->getAssessmentDate()->format('d/m Y') : null;
        }

        return $renderObject;
    }

    /**
     * @param $file
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function importFromSpreadsheet($file)
    {
        $spreadsheet = IOFactory::load($file);

        /* Expected columns in spreadsheet:
            0 => 'CAT_NAME',
            1 => 'INVENTORY_ID',
            2 => 'BILLEDE',
            3 => 'ART_TITLE',
            4 => 'ARTIST',
            5 => 'ART_YEAR',
            6 => 'ART_DIMENSION',
            7 => 'TYPE',
            8 => 'ART_EST_VALUE',
            9 => 'ART_SERIAL',
            10 => 'CUSTOM_1',
            11 => 'CUSTOM_2',
            12 => 'CUSTOM_4',
            13 => 'DEPARTMENT',
            14 => 'GEO_ROOM',
            15 => 'BUILDING',
            16 => 'REMARKS',
            17 => 'STAT_NAME',
            18 => 'STATUS_DATE',
            19 => 'INV_TYPE',
            20 => 'BARCODE',
            21 => 'PRICE',
            22 => 'INV_USER',
            23 => 'CREATION_DATE',
            24 => 'MODIFICATION_DATE',
            25 => 'SCAN_DATE',
         */
        $content = $spreadsheet->getActiveSheet()->toArray();

        foreach ($content as $entry) {
            $item = null;

            $unMappable = [];

            $barcode = !empty($entry[20]) ? str_pad($entry[20], 5, '0', STR_PAD_LEFT) : null;

            if ('Kunst' === $entry[0]) {
                $item = new Artwork();
                $item->setName($entry[3]);
                $item->setType($entry[7]);

                $item->setArtist($entry[4]);
                $item->setProductionYear($entry[5]);
                $item->setAssessmentPrice($entry[8]);
                $item->setArtSerial($entry[9]);

                // Parse ART_DIMENSION.
                $entryDimensions = strtolower($entry[6]);
                $split = explode('x', $entryDimensions);

                if (\count($split) > 1) {
                    $width = trim($split[0]);

                    if (is_numeric($width)) {
                        $item->setWidth($width);
                    }

                    $height = trim($split[1]);

                    if (is_numeric($height)) {
                        $item->setHeight($height);
                    }

                    if (3 === \count($split)) {
                        $depth = trim($split[2]);

                        if (is_numeric($depth)) {
                            $item->setDepth($depth);
                        }
                    }
                } else {
                    !empty($entryDimensions) && $unMappable[] = sprintf('ART_DIMENSION: %s', $entryDimensions);
                }
            } elseif ('Inventar' === $entry[0]) {
                $item = new Furniture();
                $item->setType($entry[19]);

                // Set name to barcode.
                $item->setName($barcode);

                !empty($entry[21]) && $unMappable[] = sprintf('INV_USER: %s', $entry[21]);
            }

            if (null !== $item) {
                $item->setBarcode($barcode);
                $item->setInventoryId($entry[1]);
                $item->setPurchasePrice($entry[21]);
                $item->setPurchasePlace($entry[12]);
                !empty($entry[11]) && $item->setPurchaseDate(\DateTime::createFromFormat('Y', $entry[11]));
                $item->setPurchasedBy($entry[10]);
                $item->setDepartment($entry[13]);
                $item->setBuilding($entry[15]);
                $item->setRoom($entry[14]);

                $item->setComment($entry[16].(\count($unMappable) > 0 ? "\n\nFrom import:\n".implode("\n - ", $unMappable) : ''));

                $this->entityManager->persist($item);
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();
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

        if (null === $width || null === $height) {
            return null;
        }
        // @TODO: Include depth, diameter and weight in string.

        return sprintf('%d x %d', $width, $height);
    }
}
