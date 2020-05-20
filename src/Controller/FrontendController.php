<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Artwork;
use App\Repository\ItemRepository;
use App\Service\TagService;
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
    private $tagService;

    /**
     * FrontendController constructor.
     *
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     * @param \App\Service\TagService                               $tagService
     */
    public function __construct(UploaderHelper $uploaderHelper, TagService $tagService)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->tagService = $tagService;
    }

    /**
     * @Route("/", name="frontend_index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\ItemRepository            $itemRepository
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ItemRepository $itemRepository, PaginatorInterface $paginator)
    {
        $form = $this->getSearchForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode($data['width']) : null;
            $height = null !== $data['height'] ? json_decode($data['height']) : null;

            $query = $itemRepository->getQuery(
                Artwork::class,
                $data['search'],
                $data['type'],
                null,
                $data['building'],
                $data['yearFrom'],
                $data['yearTo'],
                $width->min ?? null,
                $width->max ?? null,
                $height->min ?? null,
                $height->max ?? null
            );
        } else {
            $query = $itemRepository->getQuery();
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

        return (object) [
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
            'geo' => $artwork->getGeo(),
            'comment' => $artwork->getComment(),
            'description' => $artwork->getDescription(),
            'organization' => $artwork->getOrganization(),
            'department' => $artwork->getDepartment(),
            'price' => $artwork->getPurchasePrice(),
            'productionYear' => $artwork->getProductionYear(),
            'estimatedValue' => $artwork->getAssessmentPrice(),
            'estimatedValueDate' => $artwork->getAssessmentDate() ? $artwork->getAssessmentDate()->format('d/m Y') : null,
        ];
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
        if (null === $width || null === $height) {
            return null;
        }

        return sprintf('%d x %d', $width, $height);
    }

    /**
     * Create search form.
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm()
    {
        $typeChoices = $this->tagService->getChoices(Artwork::class, 'type');
        $buildingChoices = $this->tagService->getChoices(Artwork::class, 'building');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->setMethod('GET')
            ->add(
                'search',
                SearchType::class,
                [
                    'label' => 'filter.search',
                    'attr' => [
                        'placeholder' => 'filter.search_placeholder',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'filter.type',
                    'placeholder' => 'filter.type_placeholder',
                    'required' => false,
                    'choices' => $typeChoices,
                    'attr' => [
                        'class' => 'tag-select',
                    ],
                ]
            )
            ->add(
                'building',
                ChoiceType::class,
                [
                    'label' => 'filter.building',
                    'placeholder' => 'filter.building_placeholder',
                    'required' => false,
                    'choices' => $buildingChoices,
                    'attr' => [
                        'class' => 'tag-select',
                    ],
                ]
            )
            ->add(
                'width',
                ChoiceType::class,
                [
                    'label' => 'filter.width',
                    'required' => false,
                    'placeholder' => 'filter.width_placeholder',
                    'choices' => [
                        '0 - 50' => json_encode(['min' => 0, 'max' => 50]),
                        '50 - 100' => json_encode(['min' => 50, 'max' => 100]),
                        '100 <' => json_encode(['min' => 100]),
                    ],
                ]
            )
            ->add(
                'height',
                ChoiceType::class,
                [
                    'label' => 'filter.height',
                    'required' => false,
                    'placeholder' => 'filter.height_placeholder',
                    'choices' => [
                        '0 - 50' => json_encode(['min' => 0, 'max' => 50]),
                        '50 - 100' => json_encode(['min' => 50, 'max' => 100]),
                        '100 <' => json_encode(['min' => 100]),
                    ],
                ]
            )
            ->add(
                'yearFrom',
                NumberType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'filter.year_from_placeholder',
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
                        'placeholder' => 'filter.year_to_placeholder',
                    ],
                    'required' => false,
                ]
            );

        return $formBuilder->getForm();
    }
}
