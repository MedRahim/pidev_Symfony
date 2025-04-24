<?php

namespace App\Controller; // Updated namespace to include 'Rahim'

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;


#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route(name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/comment/{id}/edit', name: 'app_comment_edit', methods: ['POST'])]
    public function editComment(
        Request $request,
        Comment $comment,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Validate CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('edit-comment', $submittedToken))) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid security token'
            ], Response::HTTP_FORBIDDEN);
        }
    
        // Validate content
        $content = $request->request->get('content');
        if (empty($content)) {
            return $this->json([
                'success' => false,
                'message' => 'Comment cannot be empty'
            ], Response::HTTP_BAD_REQUEST);
        }
    
        // Update and save
        $comment->setContent($content);
        $entityManager->flush();
    
        return $this->json([
            'success' => true,
            'comment' => [
                'id' => $comment->getId(),
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('d M Y, H:i')
            ]
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Comment $comment, 
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        // Validate CSRF token
        $submittedToken = $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('delete-comment', $submittedToken))) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $entityManager->remove($comment);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error deleting comment'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}