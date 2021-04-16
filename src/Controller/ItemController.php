<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Furniture;
use App\Entity\Item;
use App\Form\ArtworkType;
use App\Form\FurnitureType;
use App\Repository\ArtworkRepository;
use App\Repository\ItemRepository;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/admin/item")
 */
class ItemController extends BaseController
{
    /**
     * @Route("/", name="item_index", methods={"GET"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\ItemRepository            $itemRepository
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return Response
     */
    public function index(Request $request, ItemRepository $itemRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->getSearchForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode($data['width']) : null;
            $height = null !== $data['height'] ? json_decode($data['height']) : null;

            $query = $itemRepository->getQuery(
                $data['itemType'],
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

        $items = [];
        /* @var Item $item */
        foreach ($pagination->getItems() as $item) {
            $items[] = $this->itemService->itemToRenderObject($item);
        }

        return $this->render(
            'admin/item/index.html.twig',
            [
                'items' => $items,
                'title' => 'Kunstdatabasen',
                'headline' => 'item.list.item',
                'brand' => 'Aarhus Kommunes kunstsamling og udsmykninger',
                'brandShort' => 'Kunstdatabasen',
                'welcome' => 'Velkommen til Aarhus Kommunes kunstsamling og udsmykninger',
                'user' => [
                    'username' => 'Admin user',
                    'email' => 'admin@email.com',
                ],
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/list/{itemType}", name="item_list", methods={"GET"})
     *
     * @param string                                    $itemType
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\ItemRepository            $itemRepository
     * @param \App\Repository\ArtworkRepository         $artworkRepository
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function list(string $itemType, Request $request, ItemRepository $itemRepository, ArtworkRepository $artworkRepository, PaginatorInterface $paginator): Response
    {
        $parameters = [];
        $parameters['display_advanced_filters'] = false;

        switch ($itemType) {
            case 'artwork':
                $itemTypeClass = Artwork::class;
                break;
            case 'furniture':
                $itemTypeClass = Furniture::class;
                break;
            default:
                $itemTypeClass = Item::class;
                break;
        }

        $form = $this->getSearchForm($itemTypeClass);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode($data['width']) : null;
            $height = null !== $data['height'] ? json_decode($data['height']) : null;

            switch ($itemType) {
                case 'artwork':
                    $query = $artworkRepository->getQuery(
                        $data['search'],
                        $data['type'],
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
                    break;
                case 'furniture':
                default:
                    $query = $itemRepository->getQuery(
                        $itemTypeClass,
                        $data['search'],
                        $data['type'],
                        null,
                        $data['building']
                    );
            }

            if (null !== $data['width'] ||
                null !== $data['height'] ||
                null !== $data['status'] ||
                null !== $data['yearFrom'] ||
                null !== $data['yearTo'] ||
                null !== $data['artistGender'] ||
                null !== $data['priceFrom'] ||
                null !== $data['priceTo']) {
                $parameters['display_advanced_filters'] = true;
            }
        } else {
            switch ($itemType) {
                case 'artwork':
                    $query = $artworkRepository->getQuery();
                    break;
                case 'furniture':
                default:
                    $query = $itemRepository->getQuery($itemTypeClass);
            }
        }

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        $items = [];
        /* @var Item $item */
        foreach ($pagination->getItems() as $item) {
            $items[] = $this->itemService->itemToRenderObject($item);
        }

        $parameters['items'] = $items;
        $parameters['supportMail'] = $this->supportMail;
        $parameters['headline'] = 'item.list.'.$itemType;
        $parameters['itemType'] = $itemType;
        $parameters['form'] = $form->createView();
        $parameters['pagination'] = $pagination;

        return $this->render(
            'admin/item/index.html.twig',
            $parameters
        );
    }

    /**
     * @Route("/{itemType}/export", name="item_export", methods={"GET"})
     *
     * @param string $itemType
     *                         The item type
     *
     * @return Response
     */
    public function export(string $itemType): Response
    {
        // Avoid php timeout errors.
        set_time_limit(0);
        $response = new StreamedResponse();

        $response->setCallback(function () use ($itemType) {
            $entityManager = $this->getDoctrine()->getManager();
            $repository = null;

            switch ($itemType) {
                case 'artwork':
                    $repository = $entityManager->getRepository(Artwork::class);
                    break;
                case 'furniture':
                    $repository = $entityManager->getRepository(Furniture::class);
                    break;
                default:
                    $repository = $entityManager->getRepository(Item::class);
            }

            $query = $repository
                ->createQueryBuilder('e')
                ->getQuery();

            $iterableItems = SimpleBatchIteratorAggregate::fromQuery(
                $query,
                100
            );

            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            $boldStyle = (new StyleBuilder())
                ->setFontBold()
                ->build();

            $serializer = new Serializer([new ObjectNormalizer()]);

            $dateCallback = function ($innerObject) {
                return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::ISO8601) : '';
            };

            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'createdAt' => $dateCallback,
                    'updatedAt' => $dateCallback,
                    'purchaseDate' => $dateCallback,
                ],
                AbstractNormalizer::ATTRIBUTES => [
                    'id',
                    'name',
                    'description',
                    'createdBy',
                    'updatedBy',
                    'createdAt',
                    'updatedAt',
                    'artist',
                    'artSerial',
                    'purchasePrice',
                    'productionYear',
                    'assessmentDate',
                    'assessmentPrice',
                    'location',
                    'building',
                    'room',
                    'address',
                    'postalCode',
                    'city',
                    'width',
                    'height',
                    'depth',
                    'diameter',
                    'weight',
                    'publiclyAccessible',
                    'status',
                    'type',
                    'organization',
                    'geo',
                    'comment',
                    'department',
                    'inventoryId',
                    'purchasePlace',
                    'barcode',
                    'purchaseDate',
                    'purchasedBy',
                    'artistGender',
                    'committeeDescription',
                    'locationDate',
                ],
            ];

            $itemsAdded = 0;

            foreach ($iterableItems as $item) {
                $itemArray = $serializer->normalize($item, null, $defaultContext);

                // Add headlines for first row.
                if (0 === $itemsAdded) {
                    $row = WriterEntityFactory::createRowFromArray(array_keys($itemArray), $boldStyle);
                    $writer->addRow($row);
                }

                // Replace null entries with empty string
                foreach ($itemArray as $key => $value) {
                    if (null === $value) {
                        $itemArray[$key] = '';
                    }
                }

                $row = WriterEntityFactory::createRowFromArray($itemArray);
                $writer->addRow($row);

                ++$itemsAdded;
            }

            $writer->close();
        });

        $filename = $itemType.'-eksport-'.date('d-m-Y');
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'.xlsx"');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{itemType}/new", name="item_new", methods={"GET","POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $itemType
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function new(Request $request, string $itemType): Response
    {
        switch ($itemType) {
            case 'artwork':
                $item = new Artwork();
                $form = $this->createForm(ArtworkType::class, $item);
                break;
            case 'furniture':
                $item = new Furniture();
                $form = $this->createForm(FurnitureType::class, $item);
                break;
            default:
                throw new \Exception('Type is not valid.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            return $this->redirectToRoute('item_list', ['itemType' => $itemType]);
        }

        return $this->render(
            'admin/item/new.html.twig',
            [
                'item' => $item,
                'itemType' => $itemType,
                'indexLink' => $this->generateUrl('item_list', ['itemType' => $itemType]),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="item_edit", methods={"GET","POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Item                          $item
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function edit(Request $request, Item $item): Response
    {
        if ($item instanceof Artwork) {
            $itemType = 'artwork';
            $form = $this->createForm(ArtworkType::class, $item);
        } elseif ($item instanceof Furniture) {
            $itemType = 'furniture';
            $form = $this->createForm(FurnitureType::class, $item);
        } else {
            throw new \Exception('Type is not valid.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item_list', ['itemType' => $itemType]);
        }

        return $this->render(
            'admin/item/edit.html.twig',
            [
                'item' => $item,
                'itemType' => $itemType,
                'indexLink' => $this->generateUrl('item_list', ['itemType' => $itemType]),
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="item_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Item                          $item
     *
     * @return Response
     */
    public function delete(Request $request, Item $item): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_index');
    }

    /**
     * @Route("/{id}/modal", name="item_modal", methods={"GET"})
     *
     * @param \App\Entity\Item $item
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getModal(Item $item)
    {
        $itemObject = $this->itemService->itemToRenderObject($item);

        $this->saveVisited();

        return new JsonResponse(
            [
                'id' => $item->getId(),
                'title' => $item->getName(),
                'editLink' => $this->generateUrl('item_edit', ['id' => $item->getId()]),
                'modalBody' => $this->renderView('admin/item/details.html.twig', ['item' => $itemObject]),
            ]
        );
    }

    /**
     * Create search form.
     *
     * @param string $classname
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getSearchForm($classname = Artwork::class)
    {
        $typeChoices = $this->tagService->getChoices($classname, 'type');
        $statusChoices = $this->tagService->getChoices($classname, 'status');
        $buildingChoices = $this->tagService->getChoices($classname, 'building');
        $artistGenderChoices = $this->tagService->getChoices($classname, 'artistGender');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->setMethod('GET')
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => 'filter.item_type',
                    'placeholder' => 'filter.item_type_placeholder',
                    'required' => false,
                    'choices' => [
                        'artwork' => 'item.artwork',
                        'furniture' => 'item.furniture',
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
            )
            ->add(
                'artistGender',
                ChoiceType::class,
                [
                    'label' => 'filter.artist_gender',
                    'required' => false,
                    'placeholder' => 'filter.artist_gender_placeholder',
                    'choices' => $artistGenderChoices,
                ]
            )
            ->add(
                'priceFrom',
                NumberType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'filter.price_from_placeholder',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'priceTo',
                NumberType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'filter.price_to_placeholder',
                    ],
                    'required' => false,
                ]
            );

        return $formBuilder->getForm();
    }
}
