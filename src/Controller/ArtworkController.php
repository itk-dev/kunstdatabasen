<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Artwork;
use App\Form\ArtworkType;
use App\Repository\ArtworkRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/artwork")
 */
class ArtworkController extends BaseController
{
    /**
     * @Route("/", name="artwork_index", methods={"GET"})
     *
     * @param \App\Repository\ArtworkRepository $artworkRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, ArtworkRepository $artworkRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->getSearchForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $width = null !== $data['width'] ? json_decode($data['width']) : null;
            $height = null !== $data['height'] ? json_decode($data['height']) : null;

            $query = $artworkRepository->getQuery(
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
            $artworks[] = $this->itemService->itemToRenderObject($artworkEntity);
        }

        return $this->render(
            'admin/artwork/index.html.twig',
            [
                'artworks' => $artworks,
                'title' => 'Kunstdatabasen',
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
     * @Route("/new", name="artwork_new", methods={"GET","POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request): Response
    {
        $artwork = new Artwork();
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($artwork);
            $entityManager->flush();

            return $this->redirectToRoute('artwork_index');
        }

        return $this->render(
            'admin/artwork/new.html.twig',
            [
                'artwork' => $artwork,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="artwork_show", methods={"GET"})
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Artwork $artwork): Response
    {
        $artwork = $this->itemService->itemToRenderObject($artwork);

        return $this->render(
            'admin/artwork/show.html.twig',
            [
                'artwork' => $artwork,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="artwork_edit", methods={"GET","POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Artwork                       $artwork
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Request $request, Artwork $artwork): Response
    {
        $form = $this->createForm(ArtworkType::class, $artwork);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('artwork_index');
        }

        return $this->render(
            'admin/artwork/edit.html.twig',
            [
                'artwork' => $artwork,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="artwork_delete", methods={"DELETE"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Artwork                       $artwork
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, Artwork $artwork): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artwork->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($artwork);
            $entityManager->flush();
        }

        return $this->redirectToRoute('artwork_index');
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
                    'label' => 'frontend.filter.building',
                    'placeholder' => 'frontend.filter.building_placeholder',
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
                    'label' => 'frontend.filter.width',
                    'required' => false,
                    'placeholder' => 'frontend.filter.width_placeholder',
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
                    'label' => 'frontend.filter.height',
                    'required' => false,
                    'placeholder' => 'frontend.filter.height_placeholder',
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


    /**
     * @Route("/{id}/modal", name="artwork_modal", methods={"GET"})
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getModal(Artwork $artwork) {
        $itemObject = $this->itemService->itemToRenderObject($artwork);

        return new JsonResponse([
            'id' => $artwork->getId(),
            'title' => $artwork->getName(),
            'editLink' => $this->generateUrl('artwork_edit', ['id' => $artwork->getId()]),
            'modalBody' => $this->renderView('admin/artwork/details.html.twig', ['artwork' => $itemObject]),
        ]);
    }
}
