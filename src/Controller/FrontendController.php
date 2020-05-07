<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Repository\ArtworkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class FrontendController extends AbstractController
{
    /**
     * @Route("/", name="frontend_index")
     *
     * @param \App\Repository\ArtworkRepository $artworkRepository
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ArtworkRepository $artworkRepository, UploaderHelper $uploaderHelper)
    {
        $artworks = [];

        $artworkEntities = $artworkRepository->findAll();

        /* @var Artwork $artworkEntity */
        foreach ($artworkEntities as $artworkEntity) {
            $path = '';
            if (count($artworkEntity->getImages()) > 0) {
                $path = $uploaderHelper->asset($artworkEntity->getImages()[0], 'imageFile');
            }

            $artworks[] = (object)[
                'link' => $this->generateUrl('frontend_artwork_show', [
                    'id' => $artworkEntity->getId(),
                ]),
                'img' => $path,
                'title' => $artworkEntity->getName(),
                'artNo' => $artworkEntity->getArtSerial(),
                'category' => '@TODO',
                'artist' => $artworkEntity->getArtist(),
                'type' => '@TODO',
                'dimensions' => '@TODO',
                'building' => '@TODO',
            ];
        }

        return $this->render('app/index.html.twig', [
            'title' => 'Kunstdatabasen',
            'data' => [
                'artworks' => $artworks,
            ],
        ]);
    }

    /**
     * @Route("/artwork/{id}", name="frontend_artwork_show", methods={"GET"})
     *
     * @param \App\Entity\Artwork $artwork
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Artwork $artwork, UploaderHelper $uploaderHelper): Response
    {
        $path = '';
        if (count($artwork->getImages()) > 0) {
            $path = $uploaderHelper->asset($artwork->getImages()[0], 'imageFile');
        }

        $artworkRender = (object)[
            'link' => $this->generateUrl('frontend_artwork_show', [
                'id' => $artwork->getId(),
            ]),
            'img' => $path,
            'title' => $artwork->getName(),
            'artNo' => $artwork->getArtSerial(),
            'category' => '@TODO',
            'artist' => $artwork->getArtist(),
            'type' => '@TODO',
            'dimensions' => '@TODO',
            'building' => '@TODO',
            'geo' => '@TODO',
            'comment' => '@TODO',
            'department' => '@TODO',
            'price' => $artwork->getPurchasePrice(),
            'productionYear' => $artwork->getProductionYear(),
            'custom1' => '@TODO',
            'custom2' => '@TODO',
            'custom4' => '@TODO',
            'estimatedValue' => $artwork->getAssessmentPrice(),
            'estimatedValueDate' => $artwork->getAssessmentDate()->format('d/m Y'),
        ];

        return $this->render('app/details.html.twig', [
            'indexLink' => $this->generateUrl('frontend_index'),
            'data' => [
                'artwork' => $artworkRender,
            ],
        ]);
    }
}
