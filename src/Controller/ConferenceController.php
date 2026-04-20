<?php

namespace App\Controller;

use App\Document\Conference;
use App\Document\Registration;
use App\Document\Session as ConferenceSession;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/conference', name: 'conference_')]
class ConferenceController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(DocumentManager $documentManager): Response
    {
        // Read all conferences for the management table.
        $conferences = $documentManager
            ->getRepository(Conference::class)
            ->findBy([], ['date' => 'ASC']);

        return $this->render('conference/index.html.twig', [
            'conferences' => $conferences,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id, DocumentManager $documentManager): Response
    {
        /** @var Conference|null $conference */
        $conference = $documentManager->find(Conference::class, $id);
        if (!$conference) {
            throw $this->createNotFoundException('Conference not found.');
        }

        // Load all sessions that belong to this conference.
        $sessions = $documentManager
            ->getRepository(ConferenceSession::class)
            ->findBy(['conference' => $conference], ['startTime' => 'ASC']);

        // Build a simple map: sessionId => number of registrations.
        $sessionRegistrationCounts = [];
        $registrationRepository = $documentManager->getRepository(Registration::class);

        foreach ($sessions as $session) {
            $sessionId = $session->getId();
            if ($sessionId === null) {
                continue;
            }

            $sessionRegistrationCounts[$sessionId] = count(
                $registrationRepository->findBy(['session' => $session])
            );
        }

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'sessions' => $sessions,
            'sessionRegistrationCounts' => $sessionRegistrationCounts,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, DocumentManager $documentManager): Response
    {
        // Handle form submit manually (without Symfony Form component).
        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $description = trim((string) $request->request->get('description', ''));
            $location = trim((string) $request->request->get('location', ''));
            $dateInput = (string) $request->request->get('date', '');

            // Basic required-field validation.
            if ($title === '' || $description === '' || $location === '' || $dateInput === '') {
                $this->addFlash('error', 'All fields are required.');

                return $this->redirectToRoute('conference_create');
            }

            // Parse HTML date input (YYYY-MM-DD) into PHP DateTime object.
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateInput);
            if (!$date) {
                $this->addFlash('error', 'Date format is invalid.');

                return $this->redirectToRoute('conference_create');
            }

            // Create and save a new conference document.
            $conference = new Conference();
            $conference
                ->setTitle($title)
                ->setDescription($description)
                ->setLocation($location)
                ->setDate($date);

            $documentManager->persist($conference);
            $documentManager->flush();

            $this->addFlash('success', 'Conference created successfully.');

            return $this->redirectToRoute('conference_index');
        }

        return $this->render('conference/create.html.twig');
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request, DocumentManager $documentManager): Response
    {
        /** @var Conference|null $conference */
        // Load document by MongoDB id from route parameter.
        $conference = $documentManager->find(Conference::class, $id);
        if (!$conference) {
            throw $this->createNotFoundException('Conference not found.');
        }

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $description = trim((string) $request->request->get('description', ''));
            $location = trim((string) $request->request->get('location', ''));
            $dateInput = (string) $request->request->get('date', '');

            // Reuse the same input validation rules as create().
            if ($title === '' || $description === '' || $location === '' || $dateInput === '') {
                $this->addFlash('error', 'All fields are required.');

                return $this->redirectToRoute('conference_edit', ['id' => $id]);
            }

            $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateInput);
            if (!$date) {
                $this->addFlash('error', 'Date format is invalid.');

                return $this->redirectToRoute('conference_edit', ['id' => $id]);
            }

            // Update existing document and flush changes.
            $conference
                ->setTitle($title)
                ->setDescription($description)
                ->setLocation($location)
                ->setDate($date);

            $documentManager->flush();

            $this->addFlash('success', 'Conference updated successfully.');

            return $this->redirectToRoute('conference_index');
        }

        return $this->render('conference/edit.html.twig', [
            'conference' => $conference,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request, DocumentManager $documentManager): Response
    {
        /** @var Conference|null $conference */
        // Find document before deleting.
        $conference = $documentManager->find(Conference::class, $id);
        if (!$conference) {
            throw $this->createNotFoundException('Conference not found.');
        }

        // Protect delete action from CSRF attacks.
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('delete_conference_'.$conference->getId(), $token)) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('conference_index');
        }

        $documentManager->remove($conference);
        $documentManager->flush();

        $this->addFlash('success', 'Conference deleted successfully.');

        return $this->redirectToRoute('conference_index');
    }
}
