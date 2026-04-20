<?php

namespace App\Command;

use App\Document\Conference;
use App\Document\Registration;
use App\Document\Session;
use App\Document\User;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-demo-data',
    description: 'Seed 10 demo records for each document type.',
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(private readonly DocumentManager $documentManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users = [];
        for ($i = 1; $i <= 10; ++$i) {
            $user = new User();
            $user
                ->setName('User '.$i)
                ->setEmail('user'.$i.'@conference.local')
                ->setPassword('password'.$i);

            $this->documentManager->persist($user);
            $users[] = $user;
        }

        $conferences = [];
        for ($i = 1; $i <= 10; ++$i) {
            $conferenceDate = new DateTimeImmutable(sprintf('+%d days', $i * 3));

            $conference = new Conference();
            $conference
                ->setTitle('Conference '.$i)
                ->setDescription('Demo description for conference '.$i)
                ->setLocation('Room '.chr(64 + $i))
                ->setDate($conferenceDate);

            $this->documentManager->persist($conference);
            $conferences[] = $conference;
        }

        $sessions = [];
        for ($i = 1; $i <= 10; ++$i) {
            $startTime = new DateTimeImmutable(sprintf('+%d days 09:00', $i * 3));
            $endTime = $startTime->modify('+90 minutes');

            $session = new Session();
            $session
                ->setTitle('Session '.$i)
                ->setStartTime($startTime)
                ->setEndTime($endTime)
                ->setConference($conferences[$i - 1]);

            $this->documentManager->persist($session);
            $sessions[] = $session;
        }

        for ($i = 0; $i < 10; ++$i) {
            $registration = new Registration();
            $registration
                ->setUser($users[$i])
                ->setSession($sessions[$i]);

            $this->documentManager->persist($registration);
        }

        $this->documentManager->flush();

        $io->success('Seed completed: 10 users, 10 conferences, 10 sessions, 10 registrations.');

        return Command::SUCCESS;
    }
}
