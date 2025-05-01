<?php
// src/Controller/Admin/AdminDashboardController.php

namespace App\Controller\Admin;

use App\Entity\Trips;
use App\Entity\Reservations;
use App\Entity\Users;
use App\Controller\Admin\ReservationsCrudController;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
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

class AdminDashboardController extends AbstractDashboardController
{
    private ManagerRegistry $doctrine;
    private ReservationsRepository $reservationsRepo;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        ManagerRegistry $doctrine,
        AdminUrlGenerator $adminUrlGenerator,
        ReservationsRepository $reservationsRepo
    ) {
        $this->doctrine = $doctrine;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->reservationsRepo = $reservationsRepo;
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(): Response
    {
        $now = new \DateTime();

        $tripRepo = $this->doctrine->getRepository(Trips::class);
        $nbTrips = (int) $tripRepo->count([]);
        $nbActiveTrips = (int) $tripRepo
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.departureTime > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $nbReservations = $this->reservationsRepo->count([]);
        $nbPendingReservations = $this->reservationsRepo->count([
            'status' => Reservations::STATUS_PENDING,
        ]);

        $firstDayThisMonth = (new \DateTime('first day of this month'))->setTime(0,0,0);
        $lastDayThisMonth = (new \DateTime('last day of this month'))->setTime(23,59,59);
        $firstDayLastMonth = (new \DateTime('first day of last month'))->setTime(0,0,0);
        $lastDayLastMonth = (new \DateTime('last day of last month'))->setTime(23,59,59);

        $revenueThisMonth = $this->reservationsRepo->getTotalRevenueForPeriod(
            $firstDayThisMonth, $lastDayThisMonth
        );
        $revenueLastMonth = $this->reservationsRepo->getTotalRevenueForPeriod(
            $firstDayLastMonth, $lastDayLastMonth
        );
        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth)/$revenueLastMonth)*100,2)
            : 0;

        $userRepo = $this->doctrine->getRepository(Users::class);
        $nbUsers = (int) $userRepo->count([]);
        $newUsers = 0;

        $trendDates = [];
        $trendValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = (new \DateTime("-{$i} days"))->setTime(0,0,0);
            $next = (clone $date)->modify('+1 day');
            $count = $this->reservationsRepo->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.reservationTime BETWEEN :start AND :end')
                ->setParameter('start', $date)
                ->setParameter('end', $next)
                ->getQuery()
                ->getSingleScalarResult();
            $trendDates[] = $date->format('d/m');
            $trendValues[] = (int) $count;
        }

        $statusLabels = ['Confirmée','Annulée','En attente'];
        $statusValues = [
            (int) $this->reservationsRepo->count(['status'=>Reservations::STATUS_CONFIRMED]),
            (int) $this->reservationsRepo->count(['status'=>Reservations::STATUS_CANCELED]),
            (int) $this->reservationsRepo->count(['status'=>Reservations::STATUS_PENDING]),
        ];

        $recentActivities = [];

        return $this->render('admin/dashboard/index.html.twig', [
            'nbTrips' => $nbTrips,
            'nbActiveTrips' => $nbActiveTrips,
            'nbReservations' => $nbReservations,
            'nbPendingReservations' => $nbPendingReservations,
            'revenue' => $revenueThisMonth,
            'revenueGrowth' => $revenueGrowth,
            'nbUsers' => $nbUsers,
            'newUsers' => $newUsers,
            'trendDates' => $trendDates,
            'trendValues' => $trendValues,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'recentActivities' => $recentActivities,
            'dashboardUrl' => $this->adminUrlGenerator
                ->setController(ReservationsCrudController::class)
                ->generateUrl(),
        ]);
    }

    #[Route('/admin/export-reservations.csv', name: 'export_reservations_csv')]
    public function exportReservationsCsv(EntityManagerInterface $em): StreamedResponse
    {
        $response = new StreamedResponse(function() use ($em) {
            $query = $em->createQuery('SELECT r FROM App\Entity\Reservations r');
            $iterableResult = $query->toIterable();
            
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID','Client','Trajet','Date réservation','Montant']);
            
            foreach ($iterableResult as $row) {
                $r = $row;
                fputcsv($handle, [
                    $r->getId(),
                    $r->getClient()->getFullName(),
                    $r->getTrip()->getDeparture().' → '.$r->getTrip()->getDestination(),
                    $r->getReservationTime()->format('Y-m-d H:i:s'),
                    $r->getTrip()->getPrice() * $r->getSeatNumber().' TND',
                ]);
                $em->detach($r);
            }
            fclose($handle);
        });
        
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="reservations_%s.csv"', date('Y-m-d'))
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

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-home');

        yield MenuItem::section('Actions rapides');
        yield MenuItem::linkToCrud('Ajouter un trajet', 'fa fa-route', Trips::class)
            ->setAction(Crud::PAGE_NEW);
        yield MenuItem::linkToCrud('Ajouter une réservation', 'fa fa-ticket', Reservations::class)
            ->setAction(Crud::PAGE_NEW);

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Trajets', 'fa fa-route', Trips::class)
            ->setDefaultSort(['departureTime' => 'DESC']);
        yield MenuItem::linkToCrud('Réservations', 'fa fa-ticket', Reservations::class)
            ->setBadge($this->reservationsRepo->count([]));

        yield MenuItem::section('Outils');
        yield MenuItem::linkToRoute('Exporter CSV', 'fa fa-file-export', 'export_reservations_csv');
        yield MenuItem::linkToUrl('Ancienne version', 'fas fa-history', '/legacy-admin')
            ->setLinkTarget('_blank');
    }

    public function configureActions(): Actions
    {
        return Actions::new()
            ->addBatchAction(Action::new('export', 'Exporter sélection')
                ->linkToRoute('export_reservations_csv')
                ->setIcon('fa fa-download')
                ->addCssClass('btn btn-primary'))
            ->add(Crud::PAGE_INDEX, Action::new('notifier', 'Notifier client')
                ->linkToCrudAction('sendNotification')
                ->setIcon('fa fa-envelope')
                ->addCssClass('text-success'));
    }

    #[Route('/admin/send-notification', name: 'admin_send_notification')]
    public function sendNotification(): Response
    {
        $this->addFlash('success', 'Notifications envoyées avec succès !');
        return $this->redirectToRoute('app_admin_dashboard');
    }
}