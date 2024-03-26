<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmployeeController extends AbstractController
{
    #[Route('/{reactRoute}', name: 'employee_control')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }
}