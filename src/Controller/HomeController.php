<?php

namespace App\Controller;

use App\Document\Conference;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_root', methods: ['GET'])]
    #[Route('/home', name: 'home_index', methods: ['GET'])]
    public function index(DocumentManager $documentManager): Response
    {
        // Fetch conferences sorted by date to display upcoming items first.
        $conferences = $documentManager
            ->getRepository(Conference::class)
            ->findBy([], ['date' => 'ASC']);

        // Render dashboard-like home page with conference cards.
        return $this->render('home/index.html.twig', [
            'conferences' => $conferences,
        ]);
    }
}
