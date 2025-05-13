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
        // Récupérer l'utilisateur connecté
       


        // Récupérer le médecin
        $medecin = $em->getRepository(Medecin::class)->find($idMedecin);
        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        // Créer un nouvel objet rendez-vous
        $rendezvous = new Rendezvous();
        $rendezvous->setMedecin($medecin);
        //$rendezvous->setUser($user); // Associer le rendez-vous à l'utilisateur connecté

        // Créer et gérer le formulaire
        $form = $this->createForm(RendezVousType::class, $rendezvous);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer le rendez-vous dans la base de données
            $em->persist($rendezvous);
            $em->flush();

            // Envoyer un email de confirmation
            $email = (new Email())
                ->from('ines.rahrah@esprit.tn') // Remplacez par l'email de l'expéditeur
                ->to('ines.rahrah@esprit.tn') // email de l'utilisateur connecté
                ->subject('Confirmation de votre rendez-vous')
                ->html('<p>Bonjour ,</p>
                        <p>Votre rendez-vous avec le docteur <strong>' . $medecin->getNomM() . '</strong> a bien été enregistré.</p>
                        <p>Date : <strong>' . $rendezvous->getDateRendezVous()->format('d/m/Y H:i') . '</strong></p>
                        <p>Merci de votre confiance.</p>');

            // Envoi de l'email
            $mailer->send($email);

            // Redirection vers une page de succès
            return $this->redirectToRoute('rendezvous_rendezvous_success');
        }

        // Afficher le formulaire
        return $this->render('FrontOffice/rendezvous_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/success', name: 'rendezvous_success')]
    public function success(): Response
    {
        return $this->render('FrontOffice/rendezvous_success.html.twig');
    }
}
