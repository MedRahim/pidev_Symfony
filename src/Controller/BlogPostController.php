<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\PostLike;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/blog')]
final class BlogPostController extends AbstractController 
{
    #[Route('/', name: 'app_blog_post_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        BlogPostRepository $blogPostRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleImageUpload($form, $blogPost);

            // Set automatic fields
            $blogPost->setCreatedAt(new \DateTimeImmutable());
            $blogPost->setPostDate(new \DateTime());
            $blogPost->setApproved(true);

            $entityManager->persist($blogPost);
            $entityManager->flush();
            $searchTerm = $request->query->get('search');
            $category = $request->query->get('category');

            return $this->redirectToRoute('app_blog_post_index');
        }

        // Fetch categories dynamically from the database
        $categories = $blogPostRepository->createQueryBuilder('b')
            ->select('b.category')
            ->distinct()
            ->getQuery()
            ->getResult();

        // Fetch recent posts
        $recentPosts = $blogPostRepository->findBy([], ['postDate' => 'DESC'], 3);

        // Fetch all blog posts
        $blogPosts = $blogPostRepository->findAll();

        // Create edit forms for each post
        $editForms = [];
        foreach ($blogPosts as $post) {
            $editForms[$post->getId()] = $this->createForm(BlogPostType::class, $post)->createView();
        }

        return $this->render('FrontOffice/blog.html.twig', [
            'form' => $form->createView(),
            'blog_posts' => $blogPosts,
            'categories' => array_column($categories, 'category'),
            'recent_posts' => $recentPosts,
            'editForms' => $editForms,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_post_show', methods: ['GET'])]
    public function show(BlogPost $blogPost): Response
    {
        return $this->json([
            'id' => $blogPost->getId(),
            'title' => $blogPost->getTitle(),
            'content' => $blogPost->getContent(),
            'category' => $blogPost->getCategory(),
            'createdAt' => $blogPost->getCreatedAt()->format('Y-m-d H:i'),
            'imageUrl' => $blogPost->getImageUrl(),
            'comments' => array_map(fn($comment) => [
                'content' => $comment->getContent(),
                'createdAt' => $comment->getCreatedAt()->format('Y-m-d H:i')
            ], $blogPost->getComments()->toArray()),
            'likes' => $blogPost->getLikes()->count(),
        ]);
    }

    #[Route('/{id}', name: 'app_blog_post_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        BlogPost $blogPost,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $blogPost->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blogPost);
            $entityManager->flush();
        }

        // Redirect to the blog index so the same page reloads
        return $this->redirectToRoute('app_blog_post_index');
    }

    #[Route('/{id}/comment', name: 'app_blog_post_comment', methods: ['POST'])]
public function comment(
    Request $request,
    BlogPost $blogPost,
    EntityManagerInterface $entityManager
): Response {
    $content = $request->request->get('comment');
    if ($content) {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setBlogPost($blogPost);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($comment);
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

    return $this->json(['success' => false]);
}
    #[Route('/{id}/like', name: 'app_blog_post_like', methods: ['POST'])]
public function like(
    BlogPost $blogPost,
    EntityManagerInterface $entityManager
): Response {
    $like = new PostLike();
    $like->setBlogPost($blogPost);
    $like->setCreatedAt(new \DateTimeImmutable());

    $entityManager->persist($like);
    $entityManager->flush();

    return $this->json([
        'success' => true,
        'likes' => $blogPost->getLikes()->count(),
        'liked' => true
    ]);
}

    #[Route('/edit/{id}', name: 'app_blog_post_edit', methods: ['POST'])]
    public function edit(
        BlogPost $blogPost,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        // Handle image upload if a new image is provided
        $file = $form->get('imageFile')->getData();
        if ($file) {
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Image upload failed.');
                return $this->redirectToRoute('app_blog_post_index');
            }
            $blogPost->setImageUrl($newFilename);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_blog_post_index');
        }

        // If not valid, reload the page so server-side validation errors are shown in the modal
        return $this->redirectToRoute('app_blog_post_index');
    }

    private function handleImageUpload($form, BlogPost $blogPost): void
    {
        $file = $form->get('imageFile')->getData();
        if ($file) {
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Image upload failed.');
                return;
            }
            $blogPost->setImageUrl($newFilename);
        }
    }
   
}