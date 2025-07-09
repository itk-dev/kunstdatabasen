<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Artwork;
use App\Repository\ArtworkRepository;
use App\Service\TagService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class FrontendController.
 */
class FrontendController extends AbstractController
{
    /**
     * FrontendController constructor.
     */
    public function __construct(
        private readonly UploaderHelper $uploaderHelper,
        private readonly TagService $tagService,
    ) {
    }

    #[Route(path: '/', name: 'frontend_index')]
    public function index(Request $request, ArtworkRepository $artworkRepository, PaginatorInterface $paginator): Response
    {
        $parameters = [];
        $parameters['display_advanced_filters'] = false;

        $form = $this->getSearchForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode((string) $data['width'], null, 512, \JSON_THROW_ON_ERROR) : null;
            $height = null !== $data['height'] ? json_decode((string) $data['height'], null, 512, \JSON_THROW_ON_ERROR) : null;

            $query = $artworkRepository->getQuery(
                $data['search'] ?? null,
                $data['type'] ?? null,
                $data['status'] ?? null,
                null,
                $data['building'] ?? null,
                $data['yearFrom'] ?? null,
                $data['yearTo'] ?? null,
                $width->min ?? null,
                $width->max ?? null,
                $height->min ?? null,
                $height->max ?? null,
                $data['artistGender'] ?? null,
                $data['priceFrom'] ?? null,
                $data['priceTo'] ?? null
            );

            if (null !== $data['width']
                || null !== $data['height']
                || null !== $data['yearFrom']
                || null !== $data['yearTo']
                || (isset($data['artistGender']) && null !== $data['artistGender'])
                || (isset($data['priceFrom']) && null !== $data['priceFrom'])
                || (isset($data['priceTo']) && null !== $data['priceTo'])) {
                $parameters['display_advanced_filters'] = true;
            }
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

        $parameters['artworks'] = $artworks;
        $parameters['pagination'] = $pagination;
        $parameters['searchForm'] = $form->createView();

        return $this->render(
            'app/index.html.twig',
            $parameters
        );
    }

    #[Route(path: '/display/{id}', name: 'frontend_artwork_show', methods: ['GET'])]
    public function show(Artwork $artwork): Response
    {
        $parameters = [
            'indexLink' => $this->generateUrl('frontend_index'),
            'data' => [
                'artwork' => $this->artworkToRenderArray($artwork),
            ],
        ];

        return $this->render(
            'app/details.html.twig',
            $parameters
        );
    }

    /**
     * Create render array for artwork.
     *
     * @return object
     */
    private function artworkToRenderArray(Artwork $artwork): object
    {
        $imagePaths = [];
        foreach ($artwork->getImages() as $image) {
            $imagePaths[] = $this->uploaderHelper->asset($image, 'imageFile');
        }

        return (object) [
            'link' => $this->generateUrl(
                'frontend_artwork_show',
                [
                    'id' => $artwork->getId(),
                ]
            ),
            'status' => $artwork->getStatus(),
            'images' => $imagePaths,
            'title' => $artwork->getName(),
            'artNo' => $artwork->getArtSerial(),
            'artist' => $artwork->getArtist(),
            'type' => $artwork->getType(),
            'dimensions' => $this->getDimensions($artwork),
            'description' => $artwork->getDescription(),
            'committeeDescription' => $artwork->getCommitteeDescription(),
            'price' => $artwork->getPurchasePrice(),
            'productionYear' => $artwork->getProductionYear(),
            'estimatedValue' => $artwork->getAssessmentPrice(),
            'estimatedValueDate' => $artwork->getAssessmentDate() ? $artwork->getAssessmentDate()->format('d/m Y') : null,
            'locationDate' => $artwork->getLocationDate(),
            'purchaseDate' => $artwork->getPurchaseDate() ? $artwork->getPurchaseDate()->format('d/m Y') : null,
            'building' => $artwork->getBuilding(),
        ];
    }

    private function getDimensions(Artwork $artwork): ?string
    {
        $width = $artwork->getWidth();
        $height = $artwork->getHeight();

        // @TODO: Include depth, diameter and weight in string.
        if (null === $width || null === $height) {
            return null;
        }

        return \sprintf('%d x %d', $width, $height);
    }

    private function getSearchForm(): FormInterface
    {
        $typeChoices = $this->tagService->getChoices(Artwork::class, 'type');
        $statusChoices = $this->tagService->getChoices(Artwork::class, 'status');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->setMethod(Request::METHOD_GET)
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
                'status',
                ChoiceType::class,
                [
                    'label' => 'filter.status',
                    'placeholder' => 'filter.status_placeholder',
                    'required' => false,
                    'choices' => $statusChoices,
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
                    'label' => 'filter.yearFrom',
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
                    'label' => 'filter.yearTo',
                    'attr' => [
                        'placeholder' => 'filter.year_to_placeholder',
                    ],
                    'required' => false,
                ]
            );

        return $formBuilder->getForm();
    }
}
