<?php

namespace App\Controller;

use App\Entity\Trips;
use App\Form\TripsType;
use App\Repository\TripsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/trips')]
class TripsController extends AbstractController
{
    #[Route('/', name: 'app_trips_index', methods: ['GET'])]
    public function index(Request $request, TripsRepository $tripsRepository): Response
    {
        $limit = 9;
        $currentPage = $request->query->getInt('page', 1);

        // Création du QueryBuilder pour filtrer
        $qb = $tripsRepository->createQueryBuilder('t');

        // Filtre par recherche globale (sur départ ou destination)
        if ($q = $request->query->get('q')) {
            $qb->andWhere('t.departure LIKE :q OR t.destination LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }
        // Filtre sur le champ departure
        if ($departure = $request->query->get('departure')) {
            $qb->andWhere('t.departure LIKE :departure')
               ->setParameter('departure', '%' . $departure . '%');
        }
        // Filtre sur le champ destination
        if ($destination = $request->query->get('destination')) {
            $qb->andWhere('t.destination LIKE :destination')
               ->setParameter('destination', '%' . $destination . '%');
        }
        // Filtre sur le prix minimum
        if ($minPrice = $request->query->get('minPrice')) {
            $qb->andWhere('t.price >= :minPrice')
               ->setParameter('minPrice', $minPrice);
        }
        // Filtre sur le prix maximum
        if ($maxPrice = $request->query->get('maxPrice')) {
            $qb->andWhere('t.price <= :maxPrice')
               ->setParameter('maxPrice', $maxPrice);
        }
        // Filtre sur la date de départ
        if ($departureDate = $request->query->get('departureDate')) {
            $date = new \DateTime($departureDate);
            $start = $date->format('Y-m-d') . ' 00:00:00';
            $end = $date->format('Y-m-d') . ' 23:59:59';
            $qb->andWhere('t.departureTime BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }
        // Filtre sur le transporteur
        if ($transport = $request->query->get('transport')) {
            $qb->andWhere('t.transportName = :transport')
               ->setParameter('transport', $transport);
        }
        // Tri
        if ($sort = $request->query->get('sort')) {
            if ($sort === 'price') {
                $qb->orderBy('t.price', 'ASC');
            } elseif ($sort === 'departureTime') {
                $qb->orderBy('t.departureTime', 'ASC');
            } elseif ($sort === 'distance') {
                $qb->orderBy('t.distance', 'ASC');
            }
        } else {
            $qb->orderBy('t.departureTime', 'ASC');
        }

        // Clone le QueryBuilder pour le comptage
        $qbCount = clone $qb;
        $totalTrips = (int) $qbCount->select('COUNT(t.id)')->getQuery()->getSingleScalarResult();

        // Pagination
        $qb->setFirstResult(($currentPage - 1) * $limit)
           ->setMaxResults($limit);
        $trips = $qb->getQuery()->getResult();
        $totalPages = ceil($totalTrips / $limit);

        // Exemple de transporteurs pour le filtre
        $transporteurs = ['Bus', 'Train', 'Metro'];

        return $this->render('FrontOffice/listing/listing.html.twig', [
            'trips'         => $trips,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'transporteurs' => $transporteurs,
        ]);
    }

    #[Route('/new', name: 'app_trips_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trips();
        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trip);
            $entityManager->flush();

            return $this->redirectToRoute('app_trips_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/trips/new.html.twig', [
            'trip' => $trip,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_trips_show', methods: ['GET'])]
    public function show(Trips $trip): Response
    {
        return $this->render('FrontOffice/listing/listing_details.html.twig', [
            'trip' => $trip,
        ]);
    }

    #[Route('/{id}/reserve', name: 'app_trips_reserve', methods: ['GET', 'POST'])]
    public function reserve(Trips $trip, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Veuillez vous connecter pour réserver ce trajet.');
            return $this->redirectToRoute('app_login');
        }

        // Ici, vous pouvez intégrer la logique de réservation.
        // Par exemple, si vous avez une entité "Reservation", vous pourriez procéder comme suit :
        //
        // $reservation = new Reservation();
        // $reservation->setUser($this->getUser());
        // $reservation->setTrip($trip);
        // $reservation->setReservedAt(new \DateTime());
        // $entityManager->persist($reservation);
        // $entityManager->flush();
        // $this->addFlash('success', 'Votre réservation a été enregistrée.');
        // return $this->redirectToRoute('app_trips_show', ['id' => $trip->getId()]);
        //
        // Pour cet exemple, nous simulons simplement une réservation réussie.
        
        $this->addFlash('success', 'Votre réservation a été enregistrée.');
        return $this->redirectToRoute('app_trips_show', ['id' => $trip->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_trips_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trips $trip, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_trips_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('FrontOffice/trips/edit.html.twig', [
            'trip' => $trip,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_trips_delete', methods: ['POST'])]
    public function delete(Request $request, Trips $trip, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trip->getId(), $request->request->get('_token'))) {
            $entityManager->remove($trip);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_trips_index', [], Response::HTTP_SEE_OTHER);
    }
}
