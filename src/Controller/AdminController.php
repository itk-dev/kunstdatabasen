<?php

namespace App\Controller;

use App\Entity\Item;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseController
{
    /**
     * @Route("/admin", name="admin")
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(SessionInterface $session)
    {
        $session->start();
        $visitedSession = $session->get('latestVisitedItems');

        $latestVisitedRender = [];

        if ($visitedSession !== null) {
            /* @var \stdClass $sessionItem */
            foreach ($visitedSession as $sessionItem) {
                /* @var Item $item */
                $item = $this->getDoctrine()->getRepository($sessionItem['type'])->find($sessionItem['id']);

                if ($item !== null) {
                    $latestVisitedRender[] = $this->itemService->itemToRenderObject($item);
                }
            }
        }

        $latestAdded = $this->getDoctrine()->getRepository(Item::class)->findBy([], ['createdAt' => 'desc'], 5);

        $latestAddedRender = [];
        foreach ($latestAdded as $item) {
            $latestAddedRender[] = $this->itemService->itemToRenderObject($item);
        }

        return $this->render('admin/dashboard.html.twig', [
            'title' => 'admin.dashboard',
            'latestVisited' => $latestVisitedRender,
            'latestAdded' => $latestAddedRender,
        ]);
    }

}
