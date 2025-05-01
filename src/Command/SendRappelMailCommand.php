<?php

namespace App\Command;

use App\Repository\Ines\RendezvousRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:send-rappel-mails',
    description: 'Envoie un email de rappel une heure avant le rendez-vous.',
)]
class SendRappelMailCommand extends Command
{
    private RendezvousRepository $rendezvousRepository;
    private MailerInterface $mailer;

    public function __construct(RendezvousRepository $rendezvousRepository, MailerInterface $mailer)
    {
        parent::__construct();
        $this->rendezvousRepository = $rendezvousRepository;
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $oneHourLater = $now->modify('+1 hour');

        $date = $oneHourLater->format('Y-m-d');
        $time = $oneHourLater->format('H:i:00');

        $qb = $this->rendezvousRepository->createQueryBuilder('r')
            ->where('r.dateRendezVous = :date')
            ->andWhere('r.timeRendezVous = :time')
            ->setParameter('date', new \DateTime($date))
            ->setParameter('time', new \DateTime($time));

        $rendezvousList = $qb->getQuery()->getResult();

        foreach ($rendezvousList as $rdv) {
            $user = $rdv->getUser();
            if ($user && $user->getEmail()) {
                $emailAddress = $user->getEmail();

                $email = (new Email())
                    ->from('ines.rahrah@esprit.tn')
                    ->to($emailAddress)
                    ->subject('Rappel de votre rendez-vous')
                    ->text("Bonjour,\n\nCeci est un rappel pour votre rendez-vous prévu à {$rdv->getTimeRendezVous()->format('H:i')} le {$rdv->getDateRendezVous()->format('d/m/Y')} au lieu : {$rdv->getLieu()}.\n\nMerci.");

                $this->mailer->send($email);
                $output->writeln("Email envoyé à " . $emailAddress);
            }
        }

        return Command::SUCCESS;
    }
}
