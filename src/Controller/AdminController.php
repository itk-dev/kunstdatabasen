<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController.
 */
class AdminController extends BaseController
{
    /**
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/admin', name: 'admin')]
    public function index(Request $request)
    {
        $session = $request->getSession();
        $session->start();
        $visitedSession = $session->get('latestVisitedItems');

        $latestVisitedRender = [];

        if (null !== $visitedSession) {
            /* @var \stdClass $sessionItem */
            foreach ($visitedSession as $sessionItem) {
                /* @var Item $item */
                $item = $this->getDoctrine()->getRepository($sessionItem['type'])->find($sessionItem['id']);

                if (null !== $item) {
                    $latestVisitedRender[] = $this->itemService->itemToRenderObject($item);
                }
            }
        }

        $latestAdded = $this->getDoctrine()->getRepository(Item::class)->findBy([], ['createdAt' => 'desc'], 5);

        $latestAddedRender = [];
        foreach ($latestAdded as $item) {
            $latestAddedRender[] = $this->itemService->itemToRenderObject($item);
        }

        return $this->render('admin/index.html.twig', [
            'title' => 'admin.index',
            'supportMail' => $this->supportMail,
            'latestVisited' => $latestVisitedRender,
            'latestAdded' => $latestAddedRender,
        ]);
    }
}
