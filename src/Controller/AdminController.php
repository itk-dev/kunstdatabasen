<?php

/*
 * This file is part of aakb/kunstdatabasen.
 * (c) 2020 ITK Development
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Repository\ItemRepository;
use App\Service\ItemService;
use App\Service\TagService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class AdminController.
 */
class AdminController extends BaseController
{
    /**
     * BaseController constructor.
     */
    public function __construct(
        string $bindSupportMail,
        RequestStack $requestStack,
        ItemService $itemService,
        TagService $tagService,
        private readonly ItemRepository $itemRepository,
    ) {
        parent::__construct($bindSupportMail, $requestStack, $itemService, $tagService);
    }

    #[Route(path: '/admin', name: 'admin')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        $session->start();
        $visitedSession = $session->get('latestVisitedItems');

        $latestVisitedRender = [];

        if (null !== $visitedSession) {
            /* @var \stdClass $sessionItem */
            foreach ($visitedSession as $sessionItem) {
                $item = $this->itemRepository->find($sessionItem['id']);
                if (null !== $item) {
                    $latestVisitedRender[] = $this->itemService->itemToRenderObject($item);
                }
            }
        }

        $latestAdded = $this->itemRepository->findBy([], ['createdAt' => 'desc'], 5);

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
