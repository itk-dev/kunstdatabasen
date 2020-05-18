<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Artwork;
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
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $router
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
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
     * @param $file
     *
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function importFromSpreadsheet($file) {
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

            if ($entry[0] === 'Kunst') {
                $item = new Artwork();
                $item->setName($entry[3]);
                $item->setArtist($entry[4]);
                $item->setProductionYear($entry[5]);
                $item->setType($entry[7]);
                $item->setAssessmentPrice($entry[8]);
                $item->setArtSerial($entry[9]);
                $item->setDepartment($entry[13]);
                $item->setBuilding($entry[15]);

                // InventoryID: 1
                // AcquisitionYear: 11

                // Parse ART_DIMENSION.
                $entryDimensions = $entry[6];
                $pattern = '/(\d*)x(\d*)$/';
                $match = preg_match($pattern, $entryDimensions, $matches);
                if ($match && count($matches) === 3) {
                    $item->setWidth($matches[1]);
                    $item->setHeight($matches[2]);
                }
                else if ($entry[6] !== '') {
                    $unMappable[] = sprintf('ART_DIMENSION: %s', $entryDimensions);
                }

                $item->setComment($entry[16] . (count($unMappable) > 0 ? "\n\nImport errors:\n" . implode("\n - ", $unMappable) : ''));
            }

            if ($item !== null) {
                $this->entityManager->persist($item);
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

        if ($width === null || $height === null) {
            return null;
        }
        // @TODO: Include depth, diameter and weight in string.

        return sprintf('%d x %d', $width, $height);
    }
}
