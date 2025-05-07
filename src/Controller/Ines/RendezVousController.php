<?php

namespace App\Controller\Ines;

use App\Entity\Ines\Rendezvous;
use App\Form\Ines\RendezVousType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\Ines\RendezvousRepository;
use App\Entity\Ines\Medecin;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

// Importer l'entité Users
use App\Entity\Ines\Users;

#[Route('/rendezvous', name: 'rendezvous_')]
class RendezVousController extends AbstractController
{
    #[Route('/new/{idMedecin}', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        int $idMedecin,
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        // Récupérer le médecin
        $medecin = $em->getRepository(Medecin::class)->find($idMedecin);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        // Créer un nouvel objet rendez-vous
        $rendezvous = new Rendezvous();
        $rendezvous->setMedecin($medecin);

        // Récupérer l'utilisateur avec l'ID 1 (utilisateur par défaut)
        $user = $em->getRepository(Users::class)->find(1);  // Utilisez `Users::class` ici
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Associer l'utilisateur au rendez-vous
        $rendezvous->setUser($user);

        // Créer et gérer le formulaire
        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Persister l'objet rendez-vous en base de données
            $em->persist($rendezvous);
            $em->flush();

            // Récupérer l'email de l'utilisateur
            $userEmail = $user->getEmail();

            // Créer l'email de confirmation
            $email = (new Email())
                ->from('ines.rahrah@esprit.tn')  // Remplacer par votre adresse email
                ->to($userEmail)  // L'email de l'utilisateur par défaut (id=1)
                ->subject('Confirmation de votre rendez-vous')
                ->text("Bonjour,\n\nVotre rendez-vous a été confirmé pour le " . 
                    $rendezvous->getDateRendezVous()->format('d/m/Y') . 
                    " à " . $rendezvous->getTimeRendezVous()->format('H:i') . 
                    " au lieu : " . $rendezvous->getLieu() . ".\n\nMerci.");

            // Envoyer l'email
            $mailer->send($email);

            // Rediriger vers une page de succès
            return $this->redirectToRoute('rendezvous_rendezvous_success');
        }

        // Rendre le formulaire
        return $this->render('FrontOffice/rendezvous_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Page de succès après la création du rendez-vous
    #[Route('/success', name: 'rendezvous_success')]
    public function success(): Response
    {
        return $this->render('FrontOffice/rendezvous_success.html.twig');
    }
}
