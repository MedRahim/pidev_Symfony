<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Trips;
use App\Entity\User;
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
    private const FIXED_USER_ID = 7;

    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    private function getFixedUser(EntityManagerInterface $em): User
    {
        $user = $em->getRepository(User::class)->find(self::FIXED_USER_ID);
        if (!$user) {
            throw new \Exception('Fixed user not found. Check that user ID 7 exists.');
        }
        return $user;
    }

    #[Route('/', name: 'app_trips_index', methods: ['GET'])]
public function index(Request $request, TripsRepository $tripsRepository): Response
{
    $currentPage = $request->query->getInt('page', 1);
    $filters = $request->query->all();

    // Utilisez le repository pour la recherche
    $trips = $tripsRepository->search(
        $filters['q'] ?? null,
        $filters['departure'] ?? null,
        $filters['destination'] ?? null,
        $filters['maxPrice'] ?? null,
        $filters['transport_type'] ?? null,
        $filters['departure_date'] ?? null,
        $filters['sort'] ?? null,
        $currentPage
    );

    $totalTrips = $tripsRepository->countByFilters([
        'q' => $filters['q'] ?? null,
        'departure' => $filters['departure'] ?? null,
        'destination' => $filters['destination'] ?? null,
        'maxPrice' => $filters['maxPrice'] ?? null,
        'transport_type' => $filters['transport_type'] ?? null,
        'departure_date' => $filters['departure_date'] ?? null
    ]);

    return $this->render('FrontOffice/listing/listing.html.twig', [
        'trips' => $trips,
        'currentPage' => $currentPage,
        'totalPages' => ceil($totalTrips / TripsRepository::PER_PAGE),
        'transporteurs' => ['Bus', 'Train', 'Avion']
    ]);
}
    #[Route('/new', name: 'app_trips_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trips();
        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('trips_images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'erreur si nécessaire
                }

                $trip->setImage($newFilename);
            }

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
    public function reserve(
        Trips $trip, 
        Request $request, 
        EntityManagerInterface $entityManager
    ): Response
    {
        // Rediriger vers la page de réservation avec l'ID du trajet
        return $this->redirectToRoute('app_reservations_new', ['tripId' => $trip->getId()]);
    }

    #[Route('/{id}/edit', name: 'app_trips_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trips $trip, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TripsType::class, $trip);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                
                try {
                    $imageFile->move(
                        $this->getParameter('trips_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gestion erreur upload
                }
                
                $trip->setImage($newFilename);
            }

            $entityManager->persist($trip);
            $entityManager->flush();

            return $this->redirectToRoute('app_trips_index');
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
    // src/Controller/TripsController.php

    #[Route('/recherche', name: 'app_trips_search', methods: ['GET'])]
    public function search(Request $request, TripsRepository $tripsRepository): Response
    {
        $filters = $request->query->all();
        
        $trips = $tripsRepository->search(
            $filters['q'] ?? null,
            $filters['departure'] ?? null,
            $filters['destination'] ?? null,
            $filters['maxPrice'] ?? null,
            $filters['transport_type'] ?? null,
            $filters['departure_date'] ?? null,
            $filters['sort'] ?? null,
            $request->query->getInt('page', 1)
        );
    
        $totalTrips = $tripsRepository->countByFilters([
            'q' => $filters['q'] ?? null,
            'departure' => $filters['departure'] ?? null,
            // ... autres filtres
        ]);
    
        return $this->render('FrontOffice/trip/_results.html.twig', [
            'trips' => $trips,
            'currentPage' => $request->query->getInt('page', 1),
            'totalPages' => ceil($totalTrips / TripsRepository::PER_PAGE)
        ]);
    }
}