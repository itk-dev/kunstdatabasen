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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/artwork")
 */
class ArtworkController extends AbstractController
{
    /**
     * @Route("/", name="artwork_index", methods={"GET"})
     *
     * @param \App\Repository\ArtworkRepository $artworkRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ArtworkRepository $artworkRepository): Response
    {
        return $this->render('artwork/index.html.twig', [
            'artworks' => $artworkRepository->findAll(),
        ]);
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

        return $this->render('artwork/new.html.twig', [
            'artwork' => $artwork,
            'form' => $form->createView(),
        ]);
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
        return $this->render('artwork/show.html.twig', [
            'artwork' => $artwork,
        ]);
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

        return $this->render('artwork/edit.html.twig', [
            'artwork' => $artwork,
            'form' => $form->createView(),
        ]);
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
}
