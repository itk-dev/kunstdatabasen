<?php

namespace App\Controller;

use App\Entity\Artwork;
use App\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class BaseController.
 */
class BaseController extends AbstractController
{
    protected $requestStack;
    protected $session;

    /**
     * BaseController constructor.
     *
     * @param RequestStack $requestStack
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(RequestStack $requestStack, SessionInterface $session)
    {
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    /**
     * Render view.
     *
     * @param string $view
     * @param array $parameters
     * @param Response|null $response
     *
     * @return Response
     */
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        !isset($parameters['title']) && $parameters['title'] = 'Kunstdatabasen';
        !isset($parameters['brand']) && $parameters['brand'] = 'Aarhus kommunes kunstdatabase';
        !isset($parameters['brandShort']) && $parameters['brandShort'] = 'Kunstdatabasen';

        $user = $this->getUser();
        $parameters['user'] = [
            'username' => $user->getUsername(),
            'email' => $user->getUsername(),
        ];

        $parameters['menuItems'] = [
            [
                'title' => 'Kunstværker',
                'icon' => 'fa-mountain',
                'active' => true,
                'link' => '/admin/artwork',
            ]
        ];

        $basePath = $this->requestStack->getCurrentRequest()->getPathInfo();

        $match = preg_match('/\/admin\/(item|artwork)\/(\d+)/', $basePath, $matches);

        if ($match) {
            $type = strtolower($matches[1]);
            switch($type) {
                case 'artwork':
                    $type = Artwork::class;
                    break;
                case 'item':
                    $type = Item::class;
                    break;
            }
            $id = $matches[2];

            $this->session->start();
            $visited = $this->session->get('latestVisitedItems', []);

            $key = array_search($id, array_column($visited, 'id'));

            if ($key) {
                array_splice($visited, $key, 1);
            }

            $visited[] = [
                'time' => time(),
                'type' => $type,
                'id' => $id,
            ];

            if (count($visited) > 5) {
                array_shift($visited);
            }

            $this->session->set('latestVisitedItems', $visited);
        }
        return parent::render($view, $parameters, $response);
    }
}
