<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    #[Route('/blog', name: 'blog_index')]
    public function index(): Response
    {
        // Logic to list blog posts
        return $this->render('blog/index.html.twig');
    }

    #[Route('/blog/{id}', name: 'blog_show')]
    public function show(int $id): Response
    {
        // Logic to show a single blog post
        return $this->render('blog/show.html.twig', [
            'id' => $id,
        ]);
    }

    #[Route('/blog/{id}/like', name: 'blog_like')]
    public function like(int $id): Response
    {
        // Logic to like a blog post
        return new Response('Blog post liked: ' . $id);
    }
}
