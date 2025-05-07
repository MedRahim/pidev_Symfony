<?php

namespace App\Controller\Ines;

use App\Entity\Ines\ServiceHospitalier;
use App\Form\Ines\ServiceHospitalierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ines\Rendezvous;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/backoffice/services-hospitaliers', name: 'backoffice_service_')]
class AdminServiceController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
public function index(
    Request $request,
    EntityManagerInterface $entityManager,
    PaginatorInterface $paginator
): Response {
    $searchTerm = $request->query->get('search');

    $queryBuilder = $entityManager->getRepository(ServiceHospitalier::class)
        ->createQueryBuilder('s');

    if ($searchTerm) {
        $queryBuilder->where('s.nomService LIKE :search')
                     ->setParameter('search', '%' . $searchTerm . '%');
    }

    $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        5
    );

    return $this->render('BackOffice/service.html.twig', [
        'services' => $pagination,
        'searchTerm' => $searchTerm,
    ]);
}


    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $service = new ServiceHospitalier();
    $form = $this->createForm(ServiceHospitalierType::class, $service);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($service);
        $entityManager->flush();

        $this->addFlash('success', 'Service ajouté avec succès !');
        return $this->redirectToRoute('backoffice_service_list');
    }

    return $this->render('BackOffice/service_new.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/edit/{idService}', name: 'edit', methods: ['GET', 'POST'])]
public function edit(Request $request, ServiceHospitalier $service, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(ServiceHospitalierType::class, $service);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Service modifié avec succès.');
        return $this->redirectToRoute('backoffice_service_list');
    }

    return $this->render('BackOffice/service_edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/delete/{idService}', name: 'delete', methods: ['POST'])]
public function delete(Request $request, ServiceHospitalier $service, EntityManagerInterface $entityManager): RedirectResponse
{
    if ($this->isCsrfTokenValid('delete' . $service->getIdService(), $request->request->get('_token'))) {
        $entityManager->remove($service);
        $entityManager->flush();
        $this->addFlash('success', 'Service supprimé avec succès.');
    }

    return $this->redirectToRoute('backoffice_service_list');
}


#[Route('/statistiques', name: 'stats')]
public function stats(EntityManagerInterface $em): Response
{
    $repo = $em->getRepository(ServiceHospitalier::class);
    $services = $repo->findAll();

    $data = [];
    $totalRdv = 0;

    foreach ($services as $service) {
        // Filtrer les rendez-vous par service via les médecins
        $count = $em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.idRendezVous)')
            ->join('r.medecin', 'm') // Rejoindre l'entité Medecin
            ->where('m.service = :service')  // Filtrer par service hospitalier du médecin
            ->setParameter('service', $service)  // $service est l'entité ServiceHospitalier
            ->getQuery()
            ->getSingleScalarResult();
    
        $totalRdv += $count;
    
        $data[] = [
            'label' => $service->getNomService(),
            'count' => (int)$count
        ];
    }

    // Calcul des pourcentages
    foreach ($data as &$item) {
        $item['percentage'] = $totalRdv > 0 ? round(($item['count'] / $totalRdv) * 100, 2) : 0;
    }

    return $this->render('BackOffice/statistiques.html.twig', [
        'stats' => $data
    ]);
}

}
