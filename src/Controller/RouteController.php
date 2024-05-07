<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RouteController extends AbstractController
{
    #[Route('/', name: 'home_control')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route(
        '/{reactRouting}',
        name: 'index',
        priority: -1,
        defaults: ['reactRouting' => null],
        requirements: ['reactRouting' => '.+']
    )]
    public function route(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/reset-password', name: 'reset_password_symfony')]
    public function passwordReset(): Response
    {
        return $this->render('default/index.html.twig');
    }
}
