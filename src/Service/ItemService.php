<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Artwork;
use App\Entity\Furniture;
use App\Entity\Image;
use App\Entity\Item;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ItemService.
 */
class ItemService
{
    /**
     * ItemService constructor.
     */
    public function __construct(
        protected readonly UploaderHelper $uploaderHelper,
        protected readonly UrlGeneratorInterface $router,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly ItemRepository $itemRepository,
        protected readonly TagService $tagService,
    ) {
    }

    /**
     * Create render object for item.
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
            'department' => $item->getDepartment(),
            'geo' => $item->getGeo(),
            'description' => $item->getDescription(),
            'comment' => $item->getComment(),
            'organization' => $item->getOrganization(),
            'status' => $item->getStatus(),
            'linkEdit' => $this->router->generate('item_edit', ['id' => $item->getId()]),
        ];

        if ($item instanceof Artwork) {
            $renderObject->artNo = $item->getArtSerial();
            $renderObject->artist = $item->getArtist();
            $renderObject->artistGender = $item->getArtistGender();
            $renderObject->dimensions = $this->getDimensions($item);
            $renderObject->price = $item->getPurchasePrice();
            $renderObject->committeeDescription = $item->getCommitteeDescription();
            $renderObject->productionYear = $item->getProductionYear();
            $renderObject->estimatedValue = $item->getAssessmentPrice();
            $renderObject->estimatedValueDate = $item->getAssessmentDate() ? $item->getAssessmentDate()->format('d/m Y') : null;
            $renderObject->locationDate = $item->getLocationDate() ? $item->getLocationDate()->format('d/m Y') : null;
            $renderObject->purchaseDate = $item->getPurchaseDate() ? $item->getPurchaseDate()->format('d/m Y') : null;
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
            A  0 => 'CAT_NAME',
            B  1 => 'INVENTORY_ID',
            C  2 => 'BILLEDE',
            D  3 => 'ART_TITLE',
            E  4 => 'ARTIST',
            F  5 => 'ART_YEAR',
            G  6 => 'ART_DIMENSION',
            H  7 => 'TYPE',
            I  8 => 'ART_EST_VALUE',
            J  9 => 'ART_SERIAL',
            K 10 => 'CUSTOM_1',
            L 11 => 'CUSTOM_2',
            M 12 => 'CUSTOM_4',
            N 13 => 'DEPARTMENT',
            O 14 => 'GEO_ROOM',
            P 15 => 'BUILDING',
            Q 16 => 'REMARKS',
            R 17 => 'STAT_NAME',
            S 18 => 'STATUS_DATE',
            T 19 => 'INV_TYPE',
            U 20 => 'BARCODE',
            V 21 => 'PRICE',
            W 22 => 'INV_USER',
            X 23 => 'CREATION_DATE',
            Y 24 => 'MODIFICATION_DATE',
            Z 25 => 'SCAN_DATE',
         */
        $content = $spreadsheet->getActiveSheet()->toArray();

        foreach ($content as $entry) {
            echo '.';

            $item = null;

            $unMappable = [];

            $barcode = !empty($entry[20]) ? str_pad((string) $entry[20], 5, '0', \STR_PAD_LEFT) : null;

            if ('Kunst' === $entry[0]) {
                $item = new Artwork();
                $item->setName($entry[3]);
                $item->setType($entry[7]);

                $item->setArtist($entry[4]);
                $item->setProductionYear($entry[5]);
                $item->setAssessmentPrice($entry[8]);
                $item->setArtSerial($entry[9]);

                // Parse ART_DIMENSION.
                $entryDimensions = mb_strtolower($entry[6] ?? '');
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
                    !empty($entryDimensions) && $unMappable[] = \sprintf('ART_DIMENSION: %s', $entryDimensions);
                }
            } elseif ('Inventar' === $entry[0]) {
                $item = new Furniture();
                $item->setType($entry[19]);

                // Set name to barcode.
                $item->setName($barcode);

                !empty($entry[21]) && $unMappable[] = \sprintf('INV_USER: %s', $entry[21]);
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
     * Import images from folder.
     *
     * The images should be named [inventoryId].jpg
     *
     * @param string $folder
     *                       The folder to import from
     */
    public function importFromImages(string $folder)
    {
        $finder = new Finder();
        $files = $finder->in($folder)->files()->name('*.jpg');

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();

            /** @var Item $item */
            $item = $this->itemRepository->findOneBy(['inventoryId' => $filename]);

            if (null !== $item) {
                try {
                    $image = new Image();
                    $image->setImageFile(new File($file->getRealPath()));
                    $image->setImageSize($file->getSize());
                    $image->setImageName('../migration_images/'.$file->getFilename());
                    $image->setPrimaryImage(true);
                    $item->addImage($image);
                    $this->entityManager->persist($image);

                    echo $filename." found. Added image.\n";
                } catch (\Exception) {
                    echo $filename." produced an error. Ignoring file.\n";
                }
            } else {
                echo $filename." not found. Ignoring file.\n";
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Get dimensions.
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

        return \sprintf('%d x %d', $width, $height);
    }
}
