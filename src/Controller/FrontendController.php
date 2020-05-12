<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Artwork;
use App\Repository\ArtworkRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class FrontendController.
 */
class FrontendController extends AbstractController
{
    private $uploaderHelper;

    /**
     * FrontendController constructor.
     *
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     */
    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    /**
     * @Route("/", name="frontend_index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\ArtworkRepository $artworkRepository
     *
     * @param \Knp\Component\Pager\PaginatorInterface $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ArtworkRepository $artworkRepository, PaginatorInterface $paginator)
    {
        $form = $this->getSearchForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = $data['width'] !== null ? json_decode($data['width']) : null;
            $height = $data['height'] !== null ? json_decode($data['height']) : null;

            $query = $artworkRepository->getQuery(
                $data['search'],
                $data['type'],
                $data['category'],
                $data['building'],
                $data['yearFrom'],
                $data['yearTo'],
                $width->min ?? null,
                $width->max ?? null,
                $height->min ?? null,
                $height->max ?? null
            );
        } else {
            $query = $artworkRepository->getQuery();
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $artworks = [];
        /* @var Artwork $artworkEntity */
        foreach ($pagination->getItems() as $artworkEntity) {
            $artworks[] = $this->artworkToRenderArray($artworkEntity);
        }

        return $this->render(
            'app/index.html.twig',
            [
                'title' => 'Kunstdatabasen',
                'artworks' => $artworks,
                'pagination' => $pagination,
                'searchForm' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/artwork/{id}", name="frontend_artwork_show", methods={"GET"})
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Artwork $artwork): Response
    {
        return $this->render(
            'app/details.html.twig',
            [
                'indexLink' => $this->generateUrl('frontend_index'),
                'data' => [
                    'artwork' => $this->artworkToRenderArray($artwork),
                ],
            ]
        );
    }

    /**
     * Create render array for artwork.
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return object
     */
    private function artworkToRenderArray(Artwork $artwork)
    {
        $path = '';
        if (\count($artwork->getImages()) > 0) {
            $path = $this->uploaderHelper->asset($artwork->getImages()[0], 'imageFile');
        }

        return (object)[
            'link' => $this->generateUrl(
                'frontend_artwork_show',
                [
                    'id' => $artwork->getId(),
                ]
            ),
            'img' => $path,
            'title' => $artwork->getName(),
            'artNo' => $artwork->getArtSerial(),
            'artist' => $artwork->getArtist(),
            'type' => $artwork->getType(),
            'dimensions' => $this->getDimensions($artwork),
            'building' => $artwork->getBuilding(),
            'geo' => '@TODO',
            'comment' => '@TODO',
            'department' => $artwork->getOrganization(),
            'price' => $artwork->getPurchasePrice(),
            'productionYear' => $artwork->getProductionYear(),
            'estimatedValue' => $artwork->getAssessmentPrice(),
            'estimatedValueDate' => $artwork->getAssessmentDate()->format('d/m Y'),
        ];
    }

    private function getDimensions(Artwork $artwork)
    {
        $width = $artwork->getWidth();
        $height = $artwork->getHeight();

        return sprintf('%d X %d', $width, $height);
    }

    /**
     * Create search form.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm()
    {

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->setMethod('GET')
            ->add(
                'search',
                SearchType::class,
                [
                    'label' => 'frontend.filter.search',
                    'attr' => [
                        'placeholder' => 'frontend.filter.search_placeholder',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'frontend.filter.type',
                    'placeholder' => 'frontend.filter.type_placeholder',
                    'required' => false,
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                    ],
                ]
            )
            ->add(
                'category',
                ChoiceType::class,
                [
                    'label' => 'frontend.filter.category',
                    'placeholder' => 'frontend.filter.category_placeholder',
                    'required' => false,
                    'choices' => [
                        '1' => '1',
                        '2' => '2',
                    ],
                ]
            )
            ->add(
                'building',
                ChoiceType::class,
                [
                    'label' => 'frontend.filter.building',
                    'placeholder' => 'frontend.filter.building_placeholder',
                    'required' => false,
                    'choices' => [
                        'building 1' => 1,
                        'building 2' => 2,
                    ],
                ]
            )
            ->add(
                'width',
                ChoiceType::class,
                [
                    'label' => 'frontend.filter.width',
                    'required' => false,
                    'placeholder' => 'frontend.filter.width_placeholder',
                    'choices' => [
                        "0 - 50" => json_encode(['min' => 0, 'max' => 50]),
                        "50 - 100" => json_encode(['min' => 50, 'max' => 100]),
                        "100 <" => json_encode(['min' => 100]),
                    ],
                ]
            )
            ->add(
                'height',
                ChoiceType::class,
                [
                    'label' => 'frontend.filter.height',
                    'required' => false,
                    'placeholder' => 'frontend.filter.height_placeholder',
                    'choices' => [
                        "0 - 50" => json_encode(['min' => 0, 'max' => 50]),
                        "50 - 100" => json_encode(['min' => 50, 'max' => 100]),
                        "100 <" => json_encode(['min' => 100]),
                    ],
                ]
            )
            ->add(
                'yearFrom',
                NumberType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'frontend.filter.year_from_placeholder',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'yearTo',
                NumberType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'frontend.filter.year_to_placeholder',
                    ],
                    'required' => false,
                ]
            );

        return $formBuilder->getForm();
    }
}
