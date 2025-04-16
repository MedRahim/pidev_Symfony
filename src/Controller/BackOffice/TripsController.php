<?php

namespace App\Controller\BackOffice;

use App\Entity\Trips;
use App\Repository\TripsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/trips')]
class TripsController extends AbstractController
{
    #[Route('/', name: 'app_admin_trips_index', methods: ['GET'])]
    public function index(Request $request, TripsRepository $tripsRepository, PaginatorInterface $paginator): Response
    {
        $query = $tripsRepository->createQueryBuilder('t')
            ->orderBy('t.departureTime', 'DESC')
            ->getQuery();
        
        $trips = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );
    
        return $this->render('backoffice/trips/index.html.twig', [
            'trips' => $trips,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_trips_show', methods: ['GET'])]
    public function show(Trips $trip): Response
    {
        return $this->render('backoffice/trips/show.html.twig', [
            'trip' => $trip,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_trips_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Trips $trip, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($trip)
            ->add('departure', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Lieu de départ'
            ])
            ->add('destination', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Destination'
            ])
            ->add('departureTime', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control datetimepicker'],
                'label' => 'Heure de départ'
            ])
            ->add('arrivalTime', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control datetimepicker'],
                'label' => 'Heure d\'arrivée'
            ])
            ->add('capacity', IntegerType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nombre de places'
            ])
            ->add('price', NumberType::class, [
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01'
                ],
                'label' => 'Prix (€)'
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Le trajet a été mis à jour avec succès.');
            return $this->redirectToRoute('app_admin_trips_show', ['id' => $trip->getId()]);
        }

        return $this->render('backoffice/trips/edit.html.twig', [
            'trip' => $trip,
            'form' => $form->createView(),
        ]);
    }
}