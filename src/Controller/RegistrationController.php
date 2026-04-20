<?php

namespace App\Controller;

use App\Document\Registration;
use App\Document\Session;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register/{sessionId}', name: 'registration_register', methods: ['GET', 'POST'])]
    public function register(string $sessionId, Request $request, DocumentManager $documentManager): Response
    {
        /** @var Session|null $session */
        $session = $documentManager->find(Session::class, $sessionId);
        if (!$session) {
            throw $this->createNotFoundException('Session not found.');
        }

        $users = $documentManager
            ->getRepository(User::class)
            ->findBy([], ['name' => 'ASC']);

        if (count($users) === 0) {
            $this->addFlash('error', 'Please create a user first.');

            return $this->redirectToRoute('session_index');
        }

        if ($request->isMethod('POST')) {
            $userId = (string) $request->request->get('userId', '');
            if ($userId === '') {
                $this->addFlash('error', 'User is required.');

                return $this->redirectToRoute('registration_register', ['sessionId' => $sessionId]);
            }

            /** @var User|null $user */
            $user = $documentManager->find(User::class, $userId);
            if (!$user) {
                $this->addFlash('error', 'Selected user was not found.');

                return $this->redirectToRoute('registration_register', ['sessionId' => $sessionId]);
            }

            $existingRegistration = $documentManager
                ->getRepository(Registration::class)
                ->findOneBy([
                    'user' => $user,
                    'session' => $session,
                ]);

            if ($existingRegistration) {
                $this->addFlash('error', 'This user is already registered for this session.');

                return $this->redirectToRoute('registration_register', ['sessionId' => $sessionId]);
            }

            $registration = new Registration();
            $registration
                ->setUser($user)
                ->setSession($session);

            $documentManager->persist($registration);
            $documentManager->flush();

            $this->addFlash('success', 'Registration completed successfully.');

            return $this->redirectToRoute('session_index');
        }

        return $this->render('registration/register.html.twig', [
            'session' => $session,
            'users' => $users,
        ]);
    }
}
