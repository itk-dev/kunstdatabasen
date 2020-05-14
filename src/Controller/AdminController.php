<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Item;
use App\Service\TagService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class AdminController extends BaseController
{
    private $uploaderHelper;

    /**
     * FrontendController constructor.
     *
     * @param \Vich\UploaderBundle\Templating\Helper\UploaderHelper $uploaderHelper
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(UploaderHelper $uploaderHelper, RequestStack $requestStack, SessionInterface $session)
    {
        $this->uploaderHelper = $uploaderHelper;

        parent::__construct($requestStack, $session);
    }

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
                $latestVisitedRender[] = $this->itemToRender($item);
            }
        }

        $latestAdded = $this->getDoctrine()->getRepository(Item::class)->findBy([], ['createdAt' => 'desc'], 5);

        $latestAddedRender = [];
        foreach ($latestAdded as $item) {
            $latestAddedRender[] = $this->itemToRender($item);
        }

        return $this->render('admin/index.html.twig', [
            'title' => 'admin.dashboard',
            'welcome' => 'Velkommen til Aarhus Kommunes kunstdatabase',
            'latestVisited' => $latestVisitedRender,
            'latestAdded' => $latestAddedRender,
        ]);
    }


    /**
     * Create render array for artwork.
     *
     * @param \App\Entity\Item $item
     *
     * @return object
     */
    private function itemToRender(Item $item)
    {
        $path = '';
        if (\count($item->getImages()) > 0) {
            $path = $this->uploaderHelper->asset($item->getImages()[0], 'imageFile');
        }

        $renderObject = (object) [
            'link' => $this->generateUrl(
                'frontend_artwork_show',
                [
                    'id' => $item->getId(),
                ]
            ),
            'img' => $path,
            'title' => $item->getName(),
            'type' => $item->getType(),
            'building' => $item->getBuilding(),
            'geo' => '@TODO',
            'comment' => '@TODO',
            'department' => $item->getOrganization(),
            'status' => $item->getStatus(),
            'linkEdit' => $this->generateUrl('artwork_edit', ['id' => $item->getId()]),
        ];

        if ($item instanceof Artwork) {
            $renderObject->artNo = $item->getArtSerial();
            $renderObject->artist = $item->getArtist();
            $renderObject->dimensions = $this->getDimensions($item);
            $renderObject->price = $item->getPurchasePrice();
            $renderObject->productionYear = $item->getProductionYear();
            $renderObject->estimatedValue = $item->getAssessmentPrice();
            $renderObject->estimatedValueDate = $item->getAssessmentDate()->format('d/m Y');
        }

        return $renderObject;
    }


    /**
     * Get dimensions.
     *
     * @param \App\Entity\Artwork $artwork
     *
     * @return string
     */
    private function getDimensions(Artwork $artwork)
    {
        $width = $artwork->getWidth();
        $height = $artwork->getHeight();

        // @TODO: Include depth, diameter and weight in string.

        return sprintf('%d X %d', $width, $height);
    }
}
