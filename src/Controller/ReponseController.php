<?php

namespace App\Controller;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/reponse')]
final class ReponseController extends AbstractController
{
    #[Route(name: 'app_reponse_index', methods: ['GET'])]
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_reponse_new_for_reclamation', methods: ['GET', 'POST'])]
    public function newForReclamation(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            if ($this->isCsrfTokenValid('reponse_new', $data['_token'])) {
                $reponse = new Reponse();
                $reponse->setReclamation($reclamation);
                $reponse->setMessage($data['message']);
                $reponse->setDatee(date('Y-m-d H:i:s'));
                
                $entityManager->persist($reponse);
                $entityManager->flush();
                
                return new JsonResponse(['success' => true]);
            }
            
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);
        $reponse->setDatee(date('Y-m-d H:i:s'));
        
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();
            
            return $this->redirectToRoute('admin_reclamation_index');
        }
        
        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
            'reclamation' => $reclamation,
        ]);
    }
    


    #[Route('/{id}', name: 'app_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            if ($this->isCsrfTokenValid('edit_reponse', $data['_token'])) {
                $reponse->setMessage($data['message']);
                $entityManager->flush();
                
                return new JsonResponse(['success' => true]);
            }
            
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_reclamation_index');
        }
        
        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true);
            
            if ($this->isCsrfTokenValid('delete', $data['_token'])) {
                $entityManager->remove($reponse);
                $entityManager->flush();
                
                return new JsonResponse(['success' => true]);
            }
            
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 400);
        }

        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}
