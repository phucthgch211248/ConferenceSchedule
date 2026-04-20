<?php

namespace App\Controller;

use App\Document\Conference;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home_index', methods: ['GET'])]
    public function index(DocumentManager $documentManager): Response
    {
        $conferences = $documentManager
            ->getRepository(Conference::class)
            ->findBy([], ['date' => 'ASC']);

        return $this->render('home/index.html.twig', [
            'conferences' => $conferences,
        ]);
    }
}
