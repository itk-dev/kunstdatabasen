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
use App\Form\ArkworkType;
use App\Form\FurnitureType;
use App\Repository\ItemRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @return \Symfony\Component\HttpFoundation\Response
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
                'brand' => 'Aarhus kommunes kunstdatabase',
                'brandShort' => 'Kunstdatabasen',
                'welcome' => 'Velkommen til Aarhus Kommunes kunstdatabase',
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
     * @param \Knp\Component\Pager\PaginatorInterface   $paginator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function list(string $itemType, Request $request, ItemRepository $itemRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->getSearchForm();

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

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode($data['width']) : null;
            $height = null !== $data['height'] ? json_decode($data['height']) : null;

            $query = $itemRepository->getQuery(
                $itemTypeClass,
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
            $query = $itemRepository->getQuery($itemTypeClass);
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
                'headline' => 'item.list.'.$itemType,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{type}/new", name="item_new", methods={"GET","POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $itemType
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function new(Request $request, string $itemType): Response
    {
        switch ($itemType) {
            case 'artwork':
                $item = new Artwork();
                $form = $this->createForm(ArkworkType::class, $item);
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

            return $this->redirectToRoute('item_index', ['itemType' => $itemType]);
        }

        return $this->render(
            'admin/item/new.html.twig',
            [
                'artwork' => $item,
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
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function edit(Request $request, Item $item): Response
    {
        if ($item instanceof Artwork) {
            $itemType = 'artwork';
            $form = $this->createForm(ArkworkType::class, $item);
        } elseif ($item instanceof Furniture) {
            $itemType = 'furniture';
            $form = $this->createForm(FurnitureType::class, $item);
        } else {
            throw new \Exception('Type is not valid.');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item_index', ['itemType' => $itemType]);
        }

        return $this->render(
            'admin/item/edit.html.twig',
            [
                'item' => $item,
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
     * @return \Symfony\Component\HttpFoundation\Response
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
