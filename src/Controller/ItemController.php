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
use Doctrine\ORM\EntityManagerInterface;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Knp\Component\Pager\PaginatorInterface;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX;
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

#[Route(path: '/admin/item')]
class ItemController extends BaseController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Repository\ItemRepository            $itemRepository
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return Response
     */
    #[Route(path: '/', name: 'item_index', methods: ['GET'])]
    public function index(Request $request, ItemRepository $itemRepository, PaginatorInterface $paginator): Response
    {
        return $this->redirectToRoute('item_list');
    }

    /**
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
    #[Route(path: '/list/{itemType}', name: 'item_list', methods: ['GET'], defaults: ['itemType' => Artwork::ITEM_TYPE])]
    public function list(string $itemType, Request $request, ItemRepository $itemRepository, ArtworkRepository $artworkRepository, PaginatorInterface $paginator): Response
    {
        $parameters = [];
        $parameters['display_advanced_filters'] = false;

        $itemTypeClass = $this->getItemTypeClass($itemType);
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

            if (null !== $data['width']
                || null !== $data['height']
                || null !== $data['status']
                || null !== $data['yearFrom']
                || null !== $data['yearTo']
                || null !== $data['artistGender']
                || null !== $data['priceFrom']
                || null !== $data['priceTo']) {
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
     * @param string $itemType
     *                         The item type
     *
     * @return Response
     */
    #[Route(path: '/{itemType}/export', name: 'item_export', methods: ['GET'])]
    public function export(string $itemType, EntityManagerInterface $entityManager): Response
    {
        // Avoid php timeout errors.
        set_time_limit(0);
        $response = new StreamedResponse();

        $itemTypeClass = $this->getItemTypeClass($itemType);
        $itemRepository = $entityManager->getRepository($itemTypeClass);

        $response->setCallback(function () use ($itemRepository) {
            $query = $itemRepository
                ->createQueryBuilder('e')
                ->getQuery();

            $iterableItems = SimpleBatchIteratorAggregate::fromQuery(
                $query,
                100
            );

            $writer = new XLSX\Writer();
            $writer->openToFile('php://output');

            $headerStyle = (new Style())
                ->setFontBold();

            $serializer = new Serializer([new ObjectNormalizer()]);

            $dateCallback = function ($innerObject) {
                return $innerObject instanceof \DateTime ? $innerObject->format(\DateTime::ATOM) : '';
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
                // We have a bug!
                $itemArray['assessmentDate'] = '';

                // Add header in first row.
                if (0 === $itemsAdded) {
                    $row = Row::fromValues(array_keys($itemArray), $headerStyle);
                    $writer->addRow($row);
                }

                // Replace null entries with empty string
                foreach ($itemArray as $key => $value) {
                    if (null === $value) {
                        $itemArray[$key] = '';
                    }
                }

                $row = Row::fromValues($itemArray);
                $writer->addRow($row);

                ++$itemsAdded;
            }

            $writer->close();
        });

        $filename = sprintf('%s-eksport-%s', $itemType, (new \DateTimeImmutable())->format('d-m-Y'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'.xlsx"');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $itemType
     *
     * @return Response
     *
     * @throws \Exception
     */
    #[Route(path: '/{itemType}/new', name: 'item_new', methods: ['GET', 'POST'])]
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Item                          $item
     *
     * @return Response
     *
     * @throws \Exception
     */
    #[Route(path: '/{id}/edit', name: 'item_edit', methods: ['GET', 'POST'])]
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Item                          $item
     *
     * @return Response
     */
    #[Route(path: '/{id}', name: 'item_delete', methods: ['DELETE'])]
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
     * @param \App\Entity\Item $item
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    #[Route(path: '/{id}/modal', name: 'item_modal', methods: ['GET'])]
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

    private function getItemTypeClass(string $itemType): string
    {
        return match ($itemType) {
            Artwork::ITEM_TYPE => Artwork::class,
            Furniture::ITEM_TYPE => Furniture::class,
            default => Item::class
        };
    }
}
