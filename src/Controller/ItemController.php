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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ItemController extends BaseController
{
    #[Route(path: '/admin/item/', name: 'item_index', methods: ['GET'])]
    public function index(ItemRepository $itemRepository, PaginatorInterface $paginator): Response
    {
        return $this->redirectToRoute('item_list');
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/admin/item/list/{itemType}', name: 'item_list', methods: ['GET'], defaults: ['itemType' => Artwork::ITEM_TYPE])]
    public function list(string $itemType, Request $request, ItemRepository $itemRepository, ArtworkRepository $artworkRepository, PaginatorInterface $paginator): Response
    {
        $parameters['display_advanced_filters'] = false;

        [$query, $form] = $this->getFilteredQuery($itemType, $request, $itemRepository, $artworkRepository, $parameters);

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
     * @param string $itemType The item type
     */
    #[Route(path: '/admin/item/{itemType}/export', name: 'item_export', methods: ['GET'])]
    public function export(string $itemType, Request $request, ItemRepository $itemRepository, ArtworkRepository $artworkRepository): Response
    {
        [$query] = $this->getFilteredQuery($itemType, $request, $itemRepository, $artworkRepository);

        // Avoid php timeout errors.
        set_time_limit(0);
        $response = new StreamedResponse();

        $callback = function () use ($query): void {
            $iterableItems = SimpleBatchIteratorAggregate::fromQuery(
                $query,
                100
            );

            $writer = new XLSX\Writer();
            $writer->openToFile('php://output');

            $headerStyle = (new Style())
                ->setFontBold();

            $serializer = new Serializer([new ObjectNormalizer()]);

            $dateCallback = fn ($innerObject) => $innerObject instanceof \DateTimeInterface ? $innerObject->format(\DateTime::ATOM) : '';

            $defaultContext = [
                AbstractNormalizer::CALLBACKS => [
                    'createdAt' => $dateCallback,
                    'updatedAt' => $dateCallback,
                    'purchaseDate' => $dateCallback,
                    'locationDate' => $dateCallback,
                    'assessmentDate' => $dateCallback,
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
        };

        $response->setCallback($callback);

        $filename = \sprintf('%s-eksport-%s', $itemType, (new \DateTimeImmutable())->format('d-m-Y'));
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'.xlsx"');
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    /**
     * @throws \Exception
     */
    #[Route(path: '/admin/item/{itemType}/new', name: 'item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, string $itemType, EntityManagerInterface $entityManager): Response
    {
        switch ($itemType) {
            case Artwork::ITEM_TYPE:
                $item = new Artwork();
                $form = $this->createForm(ArtworkType::class, $item);
                break;
            case Furniture::ITEM_TYPE:
                $item = new Furniture();
                $form = $this->createForm(FurnitureType::class, $item);
                break;
            default:
                throw new \RuntimeException('Type is not valid.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @throws \Exception
     */
    #[Route(path: '/admin/item/{id}/edit', name: 'item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item, EntityManagerInterface $entityManager): Response
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
            $entityManager->flush();

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

    #[Route(path: '/admin/item/{id}', name: 'item_delete', methods: ['DELETE'])]
    public function delete(Request $request, Item $item, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_index');
    }

    #[Route(path: '/admin/item/{id}/modal', name: 'item_modal', methods: ['GET'])]
    public function getModal(Item $item): JsonResponse
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
     */
    private function getSearchForm($classname = Artwork::class): FormInterface
    {
        $typeChoices = $this->tagService->getChoices($classname, 'type');
        $statusChoices = $this->tagService->getChoices($classname, 'status');
        $buildingChoices = $this->tagService->getChoices($classname, 'building');
        $artistGenderChoices = $this->tagService->getChoices($classname, 'artistGender');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->setMethod(Request::METHOD_GET)
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
            default => Item::class,
        };
    }

    /**
     * @return array [Query, Form]
     */
    private function getFilteredQuery(string $itemType, Request $request, ItemRepository $itemRepository, ArtworkRepository $artworkRepository, array &$parameters = []): array
    {
        $itemTypeClass = $this->getItemTypeClass($itemType);
        $form = $this->getSearchForm($itemTypeClass);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

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

            $width = null !== $data['width'] ? json_decode((string) $data['width'], null, 512, \JSON_THROW_ON_ERROR) : null;
            $height = null !== $data['height'] ? json_decode((string) $data['height'], null, 512, \JSON_THROW_ON_ERROR) : null;

            $query = match ($itemType) {
                'artwork' => $artworkRepository->getQuery(
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
                ),
                default => $itemRepository->getQuery(
                    $itemTypeClass,
                    $data['search'],
                    $data['type'],
                    null,
                    $data['building']
                ),
            };
        } else {
            $query = match ($itemType) {
                'artwork' => $artworkRepository->getQuery(),
                default => $itemRepository->getQuery($itemTypeClass),
            };
        }

        return [$query, $form];
    }
}
