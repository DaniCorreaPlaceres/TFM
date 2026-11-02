<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductMapController extends AbstractController
{
    #[Route('/documentos', name: 'product_documents', methods: ['GET'])]
    public function documents(): Response
    {
        return $this->render('producto/documentos.html.twig');
    }

    #[Route('/ayuda', name: 'product_help', methods: ['GET'])]
    public function help(): Response
    {
        return $this->render('producto/ayuda.html.twig');
    }
}
