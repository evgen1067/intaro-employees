<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VueController extends AbstractController
{
    #[
        Route('/', name: 'app_home'),
        Route('/{route}', name: 'vue_pages', requirements: ['route' => '^(?!.*(api|admin)).+'])
    ]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
