<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    #[Route('/base', name: 'base_page')]
    public function index(): Response
    {
        return $this->render('BackOffice/base.html.twig');
    }

    #[Route('/blogAdmin', name: 'blog_page')]
    public function blog(BlogPostRepository $blogPostRepository): Response
    {
        return $this->render('BackOffice/blog.html.twig', [
            'blogs' => $blogPostRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }
}