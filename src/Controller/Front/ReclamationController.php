<?php

namespace App\Controller\Front;

use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Repository\ReclamationRepository;
use App\Service\ContentFilter;
use App\Service\PriorityClassifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/reclamation')]
class ReclamationController extends AbstractController
{
    private ContentFilter $contentFilter;
    private PriorityClassifier $priorityClassifier;

    public function __construct(ContentFilter $contentFilter, PriorityClassifier $priorityClassifier)
    {
        $this->contentFilter = $contentFilter;
        $this->priorityClassifier = $priorityClassifier;
    }

    #[Route('/', name: 'app_reclamation_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = 10;
        
        $reclamations = $reclamationRepository->findBy(
            [],
            ['datee' => 'DESC'],
            $itemsPerPage,
            ($page - 1) * $itemsPerPage
        );
        
        $totalItems = $reclamationRepository->count([]);
        $totalPages = ceil($totalItems / $itemsPerPage);

        return $this->render('front/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route('/new', name: 'app_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filtrer la description avant de la sauvegarder
            $description = $reclamation->getDescription();
            if ($description) {
                $filteredDescription = $this->contentFilter->filter($description);
                $reclamation->setDescription($filteredDescription);
            }

            // Handle file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '-', $originalFilename);
                $safeFilename = strtolower($safeFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $reclamation->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de votre fichier. Veuillez réessayer.');
                    return $this->redirectToRoute('app_reclamation_new');
                }
            }

            $reclamation->setDatee((new \DateTime())->format('Y-m-d'));
            $reclamation->setState(false);

            // Déterminer la priorité
            $priority = $this->priorityClassifier->determinePriority($reclamation->getDescription());
            $reclamation->setPriorite($priority);

            $entityManager->persist($reclamation);
            $entityManager->flush();

            // Send detailed email to user
            $emailBody = "Bonjour,\n\n" .
                "Détails de votre réclamation :\n" .
                "Numéro : " . $reclamation->getId() . "\n" .
                "Type : " . $reclamation->getType() . "\n" .
                "Description : " . $reclamation->getDescription() . "\n" .
                "Date : " . $reclamation->getDatee() . "\n\n" .
                "Votre réclamation a bien été enregistrée. Nous vous contacterons dès qu'elle sera traitée.\n\nMerci de votre confiance.";

            try {
                $email = (new Email())
                    ->from('Smart City Support <dridi.mohammed01@gmail.com>')
                    ->to($form->get('email')->getData())
                    ->subject('Votre réclamation a été reçue')
                    ->text($emailBody);
                $mailer->send($email);
                $this->addFlash('success', 'Votre réclamation #' . $reclamation->getId() . ' a été enregistrée avec succès. Un email de confirmation vous a été envoyé.');
            } catch (\Exception $e) {
                $this->addFlash('warning', 'Réclamation #' . $reclamation->getId() . ' enregistrée, mais l\'email de confirmation n\'a pas pu être envoyé.');
            }

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('Front/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $oldPhoto = $reclamation->getPhoto();
        $oldType = $reclamation->getType();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Filtrer la description avant de la sauvegarder
            $description = $reclamation->getDescription();
            if ($description) {
                $filteredDescription = $this->contentFilter->filter($description);
                $reclamation->setDescription($filteredDescription);
            }

            // Handle file upload
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '-', $originalFilename);
                $safeFilename = strtolower($safeFilename);  
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
                
                try {
                    $photoFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $reclamation->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Une erreur est survenue lors du téléchargement de votre fichier. Les autres modifications ont été enregistrées.');
                }
            } else {
                $reclamation->setPhoto($oldPhoto);
            }

            if ($reclamation->getType() === null) {
                $reclamation->setType($oldType);
            }

            $reclamation->setDatee($reclamation->getDatee());
            $reclamation->setState($reclamation->isState());
            
            try {
                $entityManager->flush();
                $this->addFlash('success', 'La réclamation #' . $reclamation->getId() . ' a été modifiée avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de la modification de la réclamation. Veuillez réessayer.');
            }

            return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Front/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->get('_token'))) {
            try {
                $reclamationId = $reclamation->getId();
                
                // Delete the photo file if it exists
                if ($reclamation->getPhoto()) {
                    try {
                        $uploadsDir = str_replace('/', DIRECTORY_SEPARATOR, $this->getParameter('uploads_directory'));
                        $photoPath = $uploadsDir . DIRECTORY_SEPARATOR . $reclamation->getPhoto();
                        
                        if (file_exists($photoPath)) {
                            // Force garbage collection to release any file handles
                            gc_collect_cycles();
                            
                            // Try to delete the file
                            if (is_writable($photoPath)) {
                                if (@unlink($photoPath)) {
                                    error_log('Successfully deleted file: ' . $photoPath);
                                } else {
                                    $error = error_get_last();
                                    error_log('Failed to delete file: ' . $photoPath . '. Error: ' . ($error ? $error['message'] : 'Unknown error'));
                                }
                            } else {
                                error_log('File is not writable: ' . $photoPath);
                            }
                        }
                    } catch (\Exception $e) {
                        error_log('Error handling photo file: ' . $e->getMessage());
                    }
                }

                // The responses will be automatically deleted due to cascade removal
                $entityManager->remove($reclamation);
                $entityManager->flush();
                
                $this->addFlash('success', 'La réclamation #' . $reclamationId . ' et ses réponses associées ont été supprimées avec succès.');
                
            } catch (\Exception $e) {
                error_log('Error in delete action: ' . $e->getMessage());
                $this->addFlash('danger', 'Une erreur est survenue lors de la suppression de la réclamation. Détails: ' . $e->getMessage());
            }
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide. La suppression n\'a pas pu être effectuée pour des raisons de sécurité.');
        }

        return $this->redirectToRoute('app_reclamation_index', [], Response::HTTP_SEE_OTHER);
    }
}