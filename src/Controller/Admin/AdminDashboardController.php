<?php
// src/Controller/Admin/AdminDashboardController.php

namespace App\Controller\Admin;

use App\Entity\Trips;
use App\Entity\Reservations;
use App\Entity\User; // Supposant que vous avez cette entité
use App\Controller\Admin\ReservationsCrudController;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface; // Ajouté pour l'export
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface; // Optionnel: Pour le débogage

class AdminDashboardController extends AbstractDashboardController
{
    private ManagerRegistry $doctrine;
    private ReservationsRepository $reservationsRepo;
    private AdminUrlGenerator $adminUrlGenerator;
    private ?LoggerInterface $logger; // Optionnel: Pour le débogage

    public function __construct(
        ManagerRegistry $doctrine,
        AdminUrlGenerator $adminUrlGenerator,
        ReservationsRepository $reservationsRepo,
        ?LoggerInterface $logger = null // Injection optionnelle du logger
    ) {
        $this->doctrine = $doctrine;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->reservationsRepo = $reservationsRepo;
        $this->logger = $logger; // Stocker le logger
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        $now = new \DateTime();

        // Statistiques des trajets
        $tripRepo = $this->doctrine->getRepository(Trips::class);
        $nbTrips = (int) $tripRepo->count([]);
        $nbActiveTrips = (int) $tripRepo
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.departureTime > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        // Statistiques des réservations
        $nbReservations = $this->reservationsRepo->count([]);
        $nbPendingReservations = $this->reservationsRepo->count(['status' => Reservations::STATUS_PENDING]); // Utiliser la constante

        // Tendance des 7 derniers jours
        $trendDates = [];
        $trendValues = [];
        try {
            for ($i = 6; $i >= 0; $i--) {
                // Utiliser 'today' pour s'assurer qu'on est bien au début de la journée dans le fuseau horaire de PHP
                $date = new \DateTimeImmutable("today -{$i} days"); // Utiliser DateTimeImmutable est souvent plus sûr
                $next = $date->modify('+1 day');

                // Assurez-vous que reservationTime est bien l'heure de création de la réservation
                $count = $this->reservationsRepo->createQueryBuilder('r')
                    ->select('COUNT(r.id)')
                    ->where('r.reservationTime >= :start AND r.reservationTime < :end') // Utiliser >= et < est plus sûr pour les intervalles de temps
                    ->setParameter('start', $date)
                    ->setParameter('end', $next)
                    ->getQuery()
                    ->getSingleScalarResult();

                $trendDates[] = $date->format('d/m');
                $trendValues[] = (int)$count;
            }
        } catch (\Exception $e) {
            // Log l'erreur si le logger est disponible
            $this->logger?->error('Erreur lors du calcul de la tendance des réservations: ' . $e->getMessage());
            // Initialiser avec des valeurs vides pour éviter une erreur Twig
            $trendDates = [];
            $trendValues = [];
            $this->addFlash('danger', 'Impossible de calculer la tendance des réservations.');
        }


        // Répartition des statuts
        $statusLabels = ['Confirmées', 'Annulées', 'En attente'];
        $statusValues = [];
        try {
             $statusValues = [
                $this->reservationsRepo->count(['status' => Reservations::STATUS_CONFIRMED]),
                $this->reservationsRepo->count(['status' => Reservations::STATUS_CANCELED]), // *** CORRECTION ICI *** Utiliser la constante correcte
                $this->reservationsRepo->count(['status' => Reservations::STATUS_PENDING]),
            ];
        } catch (\Exception $e) {
            $this->logger?->error('Erreur lors du calcul des statuts des réservations: ' . $e->getMessage());
            $statusValues = [0, 0, 0]; // Valeurs par défaut en cas d'erreur
             $this->addFlash('danger', 'Impossible de calculer les statuts des réservations.');
        }

        // ----- Débogage Optionnel -----
        // Décommentez pour voir les valeurs calculées avant de les envoyer au template
        /*
        if ($this->logger) {
             $this->logger->info('Dashboard Data:', [
                'trendDates' => $trendDates,
                'trendValues' => $trendValues,
                'statusLabels' => $statusLabels,
                'statusValues' => $statusValues,
             ]);
        }
        // Ou utiliser dump() si vous avez accès à la sortie directe (environnement de dev)
        // dump($trendDates, $trendValues, $statusLabels, $statusValues);
        */
        // ----- Fin Débogage -----


        // Activité récente (Votre code existant - semble fonctionner)
        $lastReservations = [];
        $recentActivities = [];
         try {
             $lastReservations = $this->reservationsRepo->findBy([], ['reservationTime' => 'DESC'], 5);
             $recentActivities = array_map(function(Reservations $r) { // Typer la variable $r
                 $trip = $r->getTrip(); // Récupérer le trajet une fois
                 $client = $r->getUser(); // Supposant que getUser() renvoie l'utilisateur
                 $amount = $trip ? ($trip->getPrice() * $r->getSeatNumber()) : 0;

                 return [
                     // Modifier la partie de la génération du titre :
'title' => 'Réservation #' . $r->getId() . ($client ? ' par ' . $client : ''),
                     'description' => sprintf(
                         '%d siège(s) pour %s → %s (%s TND) - Statut: %s',
                         $r->getSeatNumber(),
                         $trip ? $trip->getDeparture() : 'N/A', // Gérer le cas où le trajet est null
                         $trip ? $trip->getDestination() : 'N/A',
                         number_format((float)$amount, 2, ',', '.'), // S'assurer que le montant est un float
                         $r->getStatus() ?? 'N/A' // Gérer le statut null
                     ),
                     'date' => $r->getReservationTime(),
                     'url' => $this->adminUrlGenerator // Ajouter un lien vers le détail si possible
                        ->setController(ReservationsCrudController::class)
                        ->setAction(Action::DETAIL)
                        ->setEntityId($r->getId())
                        ->generateUrl()
                 ];
             }, $lastReservations);
         } catch (\Exception $e) {
             $this->logger?->error('Erreur lors de la récupération des activités récentes: ' . $e->getMessage());
             $this->addFlash('danger', 'Impossible de charger les activités récentes.');
         }


        return $this->render('admin/dashboard/index.html.twig', [
            'nbTrips' => $nbTrips,
            'nbActiveTrips' => $nbActiveTrips,
            'nbReservations' => $nbReservations,
            'nbPendingReservations' => $nbPendingReservations,
            // Passer les données JSON encodées directement évite des erreurs dans Twig si les variables sont vides
            'trendDatesJson' => json_encode($trendDates),
            'trendValuesJson' => json_encode($trendValues),
            'statusLabelsJson' => json_encode($statusLabels),
            'statusValuesJson' => json_encode($statusValues),
            'recentActivities' => $recentActivities,
            // Note: 'dashboardUrl' n'est pas utilisé dans le template fourni, mais laissé ici au cas où.
            'dashboardUrl' => $this->adminUrlGenerator
                ->setController(ReservationsCrudController::class)
                ->generateUrl(),
        ]);
    }

    // --- Le reste de vos méthodes (export, configureDashboard, configureMenuItems, configureActions, sendNotification) reste identique ---
    // Assurez-vous que l'EntityManagerInterface est injecté si exportReservationsCsv est utilisé
    #[Route('/admin/export-reservations.csv', name: 'export_reservations_csv')]
    public function exportReservationsCsv(EntityManagerInterface $em): StreamedResponse // Injection de EM ici
    {
        // ... (votre code d'export existant)
        // Assurez-vous que $r->getClient() et $r->getTrip() existent et renvoient des objets
        // et que $client->getFullName() existe sur l'entité User/Client.
         $response = new StreamedResponse(function() use ($em) {
            // Utiliser le repository pour une meilleure abstraction
            $iterableResult = $this->reservationsRepo->createQueryBuilder('r')
                ->leftJoin('r.user', 'u') // Joindre l'utilisateur
                ->leftJoin('r.trip', 't') // Joindre le trajet
                ->select('r, u, t') // Sélectionner toutes les entités nécessaires
                ->getQuery()
                ->toIterable();

            $handle = fopen('php://output', 'w');
            // Ajouter l'entête UTF-8 BOM pour une meilleure compatibilité Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['ID','Client','Email Client', 'Trajet','Date réservation', 'Nb Sièges', 'Type Siège', 'Statut', 'Statut Paiement', 'Montant (TND)']); // Colonnes plus détaillées

            foreach ($iterableResult as $reservation) {
                 // $reservation est maintenant l'entité Reservation complète
                $trip = $reservation->getTrip();
                $user = $reservation->getUser(); // Assurez-vous que c'est la bonne relation
                $amount = ($trip && $reservation->getSeatNumber()) ? (float)$trip->getPrice() * $reservation->getSeatNumber() : 0;

                 fputcsv($handle, [
                    $reservation->getId(),
                    $user ? $user->getFullName() : 'N/A', // Utiliser la méthode de l'entité User
                    $user ? $user->getEmail() : 'N/A', // Exemple: email utilisateur
                    $trip ? $trip->getDeparture().' → '.$trip->getDestination() : 'N/A',
                    $reservation->getReservationTime() ? $reservation->getReservationTime()->format('Y-m-d H:i:s') : 'N/A',
                    $reservation->getSeatNumber(),
                    $reservation->getSeatType(),
                    $reservation->getStatus(),
                    $reservation->getPaymentStatus(),
                    number_format($amount, 2, '.', '') // Format numérique simple pour CSV
                 ]);
                $em->detach($reservation); // Détacher pour libérer la mémoire
                 if ($trip) $em->detach($trip);
                 if ($user) $em->detach($user);
            }
            fclose($handle);
         });

         $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
         $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="reservations_%s.csv"', date('Y-m-d_His')) // Ajouter l'heure pour unicité
         );
         return $response;
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin Transport <sup>v2.0</sup>')
            ->setFaviconPath('favicon-admin.png')
            ->renderContentMaximized();
    }

   // src/Controller/Admin/AdminDashboardController.php
public function configureMenuItems(): iterable
{
    yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

    yield MenuItem::section('Gestion');
    yield MenuItem::linkToCrud('Trajets', 'fa fa-route', Trips::class);
    yield MenuItem::linkToCrud('Réservations', 'fa fa-ticket', Reservations::class);

    yield MenuItem::section('Exports');
    yield MenuItem::linkToRoute('Export Réservations', 'fas fa-file-csv', 'export_reservations_csv')
        ->setLinkTarget('_blank');
    
    yield MenuItem::linkToRoute('Export Trajets', 'fas fa-file-csv', 'export_trips_csv')
        ->setLinkTarget('_blank');

    yield MenuItem::section('Administration');
    yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
}

     public function configureActions(): Actions // Pas besoin de la redéfinir si pas de changement
     {
         return parent::configureActions() // Hériter des actions par défaut
            // Ajouter des actions globales si nécessaire
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
             // L'action d'export est mieux gérée dans ReservationsCrudController si elle est spécifique
             // L'action 'notifier' doit être ajoutée au CRUD concerné (ex: ReservationsCrudController)
     }

    // Conserver l'action de notification si elle est globale, sinon la déplacer
     #[Route('/admin/send-notification', name: 'admin_send_notification')]
     public function sendNotification(): Response // Doit être liée à une action spécifique
     {
         // Mettre ici la vraie logique d'envoi de notification
         $this->addFlash('warning', 'Logique de notification non implémentée.'); // Message d'avertissement
         // Rediriger vers le dashboard ou la page précédente
         return $this->redirectToRoute('app_admin_dashboard');
     }
}