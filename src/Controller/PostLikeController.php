<?php

namespace App\Controller; // Updated namespace to include 'Rahim'

use App\Entity\PostLike;
use App\Form\PostLikeType;
use App\Repository\PostLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/post/like')]
final class PostLikeController extends AbstractController
{
    #[Route(name: 'app_post_like_index', methods: ['GET'])]
    public function index(PostLikeRepository $postLikeRepository): Response
    {
        return $this->render('post_like/index.html.twig', [
            'post_likes' => $postLikeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_post_like_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $postLike = new PostLike();
        $form = $this->createForm(PostLikeType::class, $postLike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($postLike);
            $entityManager->flush();

            return $this->redirectToRoute('app_post_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post_like/new.html.twig', [
            'post_like' => $postLike,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_like_show', methods: ['GET'])]
    public function show(PostLike $postLike): Response
    {
        return $this->render('post_like/show.html.twig', [
            'post_like' => $postLike,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_like_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PostLike $postLike, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostLikeType::class, $postLike);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_post_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post_like/edit.html.twig', [
            'post_like' => $postLike,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_like_delete', methods: ['POST'])]
    public function delete(Request $request, PostLike $postLike, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$postLike->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($postLike);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_like_index', [], Response::HTTP_SEE_OTHER);
    }
}
