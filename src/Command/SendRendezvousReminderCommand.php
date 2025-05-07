<?php

namespace App\Command;

use App\Service\Ines\TwilioService;
use App\Repository\Ines\RendezvousRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendRendezvousReminderCommand extends Command
{
    protected static $defaultName = 'rendezvous:send-reminder';

    private $rendezvousRepository;
    private $twilioService;

    public function __construct(RendezvousRepository $rendezvousRepository, TwilioService $twilioService)
    {
        parent::__construct();
        $this->rendezvousRepository = $rendezvousRepository;
        $this->twilioService = $twilioService;
    }

    protected function configure()
    {
        $this->setDescription('Envoie un rappel par SMS aux patients 1h avant leur rendez-vous.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTime('now', new \DateTimeZone('Africa/Tunis'));
        $oneHourLater = (clone $now)->modify('+1 hour');
    
        $startTime = (clone $oneHourLater)->modify('-1 minute')->format('H:i:s');
        $endTime = (clone $oneHourLater)->modify('+1 minute')->format('H:i:s');
    
        $output->writeln("⏰ Recherche de rendez-vous pour " . $oneHourLater->format('Y-m-d') . " entre $startTime et $endTime");
    
        $rendezvousList = $this->rendezvousRepository->createQueryBuilder('r')
            ->where('r.dateRendezVous = :date')
            ->andWhere('r.timeRendezVous BETWEEN :start AND :end')
            ->setParameter('date', $oneHourLater->format('Y-m-d'))
            ->setParameter('start', $startTime)
            ->setParameter('end', $endTime)
            ->getQuery()
            ->getResult();
    
        $output->writeln("📋 Rendez-vous trouvés : " . count($rendezvousList));
    
        foreach ($rendezvousList as $rdv) {
            if ($rdv->getStatus() === 'confirmé') {
                $user = $rdv->getUser();
                $phoneNumber = $user->getPhoneNumber();
    
                if ($phoneNumber) {
                    $message = sprintf(
                        "Rappel : Votre rendez-vous avec le Dr. %s est à %s dans 1 heure.",
                        $rdv->getMedecin()->getNomM(),
                        $rdv->getLieu()
                    );
                    $this->twilioService->sendSms($phoneNumber, $message);
                    $output->writeln("✅ SMS envoyé à $phoneNumber");
                } else {
                    $output->writeln("⚠️ Numéro manquant pour l'utilisateur " . $user->getNom());
                }
            }
        }
    
        return Command::SUCCESS;
    }
    
}
