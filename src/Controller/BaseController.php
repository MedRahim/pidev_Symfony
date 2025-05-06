<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends AbstractController
{
    #[Route('/base', name: 'base_page')]
    public function index(): Response
    {
        return $this->render('BackOffice/base.html.twig');
    }

    #[Route('/blogAdmin', name: 'blog_page')]
    public function blog(BlogPostRepository $blogPostRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $blogPostRepository->createQueryBuilder('b')
            ->orderBy('b.createdAt', 'DESC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            10 // Number of items per page
        );

        return $this->render('BackOffice/blog.html.twig', [
            'blogs' => $pagination,
        ]);
    }
}