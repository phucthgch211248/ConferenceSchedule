<?php

namespace App\Controller;

use App\Document\Conference;
use App\Document\Session as ConferenceSession;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/session', name: 'session_')]
class SessionController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(DocumentManager $documentManager): Response
    {
        // Show sessions in chronological order.
        $sessions = $documentManager
            ->getRepository(ConferenceSession::class)
            ->findBy([], ['startTime' => 'ASC']);

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, DocumentManager $documentManager): Response
    {
        // Sessions must belong to a conference, so load conference choices.
        $conferences = $documentManager
            ->getRepository(Conference::class)
            ->findBy([], ['date' => 'ASC']);

        // Block session creation when there is no parent conference yet.
        if (count($conferences) === 0) {
            $this->addFlash('error', 'Please create a conference first.');

            return $this->redirectToRoute('conference_create');
        }

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $startTimeInput = (string) $request->request->get('startTime', '');
            $endTimeInput = (string) $request->request->get('endTime', '');
            $conferenceId = (string) $request->request->get('conferenceId', '');

            // Basic required-field validation.
            if ($title === '' || $startTimeInput === '' || $endTimeInput === '' || $conferenceId === '') {
                $this->addFlash('error', 'All fields are required.');

                return $this->redirectToRoute('session_create');
            }

            // Parse datetime-local input format: YYYY-MM-DDTHH:MM.
            $startTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $startTimeInput);
            $endTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $endTimeInput);
            if (!$startTime || !$endTime) {
                $this->addFlash('error', 'Date time format is invalid.');

                return $this->redirectToRoute('session_create');
            }

            // Business rule: end time must be after start time.
            if ($endTime <= $startTime) {
                $this->addFlash('error', 'End time must be after start time.');

                return $this->redirectToRoute('session_create');
            }

            /** @var Conference|null $conference */
            // Resolve selected conference reference.
            $conference = $documentManager->find(Conference::class, $conferenceId);
            if (!$conference) {
                $this->addFlash('error', 'Conference not found.');

                return $this->redirectToRoute('session_create');
            }

            // Save session linked to its parent conference.
            $session = new ConferenceSession();
            $session
                ->setTitle($title)
                ->setStartTime($startTime)
                ->setEndTime($endTime)
                ->setConference($conference);

            $documentManager->persist($session);
            $documentManager->flush();

            $this->addFlash('success', 'Session created successfully.');

            return $this->redirectToRoute('session_index');
        }

        return $this->render('session/create.html.twig', [
            'conferences' => $conferences,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request, DocumentManager $documentManager): Response
    {
        /** @var ConferenceSession|null $session */
        // Load session to edit.
        $session = $documentManager->find(ConferenceSession::class, $id);
        if (!$session) {
            throw $this->createNotFoundException('Session not found.');
        }

        $conferences = $documentManager
            ->getRepository(Conference::class)
            ->findBy([], ['date' => 'ASC']);

        if (count($conferences) === 0) {
            $this->addFlash('error', 'Please create a conference first.');

            return $this->redirectToRoute('conference_create');
        }

        if ($request->isMethod('POST')) {
            $title = trim((string) $request->request->get('title', ''));
            $startTimeInput = (string) $request->request->get('startTime', '');
            $endTimeInput = (string) $request->request->get('endTime', '');
            $conferenceId = (string) $request->request->get('conferenceId', '');

            // Reuse create() validations for consistency.
            if ($title === '' || $startTimeInput === '' || $endTimeInput === '' || $conferenceId === '') {
                $this->addFlash('error', 'All fields are required.');

                return $this->redirectToRoute('session_edit', ['id' => $id]);
            }

            $startTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $startTimeInput);
            $endTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $endTimeInput);
            if (!$startTime || !$endTime) {
                $this->addFlash('error', 'Date time format is invalid.');

                return $this->redirectToRoute('session_edit', ['id' => $id]);
            }

            if ($endTime <= $startTime) {
                $this->addFlash('error', 'End time must be after start time.');

                return $this->redirectToRoute('session_edit', ['id' => $id]);
            }

            /** @var Conference|null $conference */
            $conference = $documentManager->find(Conference::class, $conferenceId);
            if (!$conference) {
                $this->addFlash('error', 'Conference not found.');

                return $this->redirectToRoute('session_edit', ['id' => $id]);
            }

            // Update and persist changes.
            $session
                ->setTitle($title)
                ->setStartTime($startTime)
                ->setEndTime($endTime)
                ->setConference($conference);

            $documentManager->flush();

            $this->addFlash('success', 'Session updated successfully.');

            return $this->redirectToRoute('session_index');
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'conferences' => $conferences,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request, DocumentManager $documentManager): Response
    {
        /** @var ConferenceSession|null $session */
        // Load session before delete.
        $session = $documentManager->find(ConferenceSession::class, $id);
        if (!$session) {
            throw $this->createNotFoundException('Session not found.');
        }

        // Protect delete action from CSRF attacks.
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('delete_session_'.$session->getId(), $token)) {
            $this->addFlash('error', 'Invalid CSRF token.');

            return $this->redirectToRoute('session_index');
        }

        $documentManager->remove($session);
        $documentManager->flush();

        $this->addFlash('success', 'Session deleted successfully.');

        return $this->redirectToRoute('session_index');
    }
}
