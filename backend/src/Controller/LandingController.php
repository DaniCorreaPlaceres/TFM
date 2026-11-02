<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LandingController extends AbstractController
{
    #[Route('/', name: 'landing', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('inicio/portada.html.twig');
    }
}
