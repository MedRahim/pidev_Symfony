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
use Knp\Component\Pager\PaginatorInterface;
use App\Service\ContentFilterService;
use App\Service\NewsApiService;
use App\Service\HuggingFaceService;




#[Route('/blog')]
final class BlogPostController extends AbstractController 
{
    #[Route('/', name: 'app_blog_post_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        BlogPostRepository $blogPostRepository,
        EntityManagerInterface $entityManager,
        \Knp\Component\Pager\PaginatorInterface $paginator,
        NewsApiService $newsApiService
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

            return $this->redirectToRoute('app_blog_post_index');
        }

        // Get filter parameters
        $searchTerm = $request->query->get('search');
        $currentCategory = $request->query->get('category');
        $selectedRecent = $request->query->get('recent');

        // Fetch categories dynamically from the database
        $categories = $blogPostRepository->createQueryBuilder('b')
            ->select('b.category')
            ->distinct()
            ->where('b.category IS NOT NULL')
            ->getQuery()
            ->getResult();

        // Fetch recent posts
        $recentPosts = $blogPostRepository->findBy(
            [], 
            ['postDate' => 'DESC'], 
            3
        );

        // Build the query based on filters
        $queryBuilder = $blogPostRepository->createQueryBuilder('b')
            ->orderBy('b.postDate', 'DESC');

        // Apply category filter if set
        if ($currentCategory) {
            $queryBuilder->andWhere('b.category = :category')
                ->setParameter('category', $currentCategory);
        }

        // Apply search filter if set
        if ($searchTerm) {
            $queryBuilder->andWhere('b.title LIKE :searchTerm OR b.content LIKE :searchTerm')
                ->setParameter('searchTerm', '%' . $searchTerm . '%');
        }

        // Apply recent post filter if set
        if ($selectedRecent) {
            $queryBuilder->andWhere('b.id = :recentId')
                ->setParameter('recentId', $selectedRecent);
        }

        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // Number of items per page
        );

        // Create edit forms for each post
        $editForms = [];
        foreach ($pagination as $post) {
            $editForms[$post->getId()] = $this->createForm(BlogPostType::class, $post)->createView();
        }

        // Fetch news articles and ensure we always have an array
        $newsQuery = $currentCategory ?: $searchTerm ?: '';
        $newsArticles = $newsApiService->fetchNews($newsQuery, 5);
        
        // If we got an error message instead of articles array, use empty array
        if (!is_array($newsArticles) || (is_array($newsArticles) && isset($newsArticles['message']))) {
            $newsArticles = [];
        }

        return $this->render('FrontOffice/blog.html.twig', [
            'form' => $form->createView(),
            'blog_posts' => $pagination,
            'categories' => array_column($categories, 'category'),
            'recent_posts' => $recentPosts,
            'editForms' => $editForms,
            'current_category' => $currentCategory,
            'selected_recent' => $selectedRecent,
            'search_term' => $searchTerm,
            'news_articles' => $newsArticles,
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
        // Verify CSRF token
        if (!$this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ], 403);
        }

        try {
            $entityManager->remove($blogPost);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error deleting post'
            ], 500);
        }
    }

    #[Route('/{id}/comment', name: 'app_blog_post_comment', methods: ['POST'])]
public function comment(
    Request $request,
    BlogPost $blogPost,
    EntityManagerInterface $entityManager,
    ContentFilterService $contentFilter
): Response {
    $content = $request->request->get('comment');
    
    if (empty($content)) {
        return $this->json(['success' => false, 'message' => 'Comment cannot be empty']);
    }

    // Check for bad words
    if ($contentFilter->containsBadWords($content)) {
        return $this->json([
            'success' => false,
            'message' => 'Your comment contains inappropriate language'
        ]);
    }

    // Existing code to save comment
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
    Request $request,
    BlogPost $blogPost,
    EntityManagerInterface $entityManager
): Response {
    // Debug request data
    error_log(print_r($request->request->all(), true));
    error_log(print_r($_FILES, true));

    // Verify CSRF token
    if (!$this->isCsrfTokenValid('blog_post', $request->request->get('_token'))) {
        return $this->json([
            'success' => false,
            'message' => 'Invalid CSRF token'
        ], 403);
    }

    // Handle partial updates
    $isUpdated = false;

    if ($request->request->has('title')) {
        $blogPost->setTitle($request->request->get('title'));
        $isUpdated = true;
    }

    if ($request->request->has('content')) {
        $blogPost->setContent($request->request->get('content'));
        $isUpdated = true;
    }

    if ($request->request->has('category')) {
        $blogPost->setCategory($request->request->get('category'));
        $isUpdated = true;
    }

    // Handle image upload if present
    if ($request->files->has('imageFile')) {
        $file = $request->files->get('imageFile');
        if ($file) {
            $newFilename = uniqid().'.'.$file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $blogPost->setImageUrl($newFilename);
                $isUpdated = true;
            } catch (FileException $e) {
                return $this->json([
                    'success' => false,
                    'message' => 'File upload failed'
                ], 400);
            }
        }
    }

    if ($isUpdated) {
        try {
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'post' => [
                    'id' => $blogPost->getId(),
                    'title' => $blogPost->getTitle(),
                    'content' => $blogPost->getContent(),
                    'category' => $blogPost->getCategory(),
                    'imageUrl' => $blogPost->getImageUrl()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error updating post'
            ], 500);
        }
    }

    return $this->json([
        'success' => true,
        'message' => 'No changes made'
    ]);
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

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
    #[Route('/blog/generate-content', name: 'app_generate_content', methods: ['POST'])]
public function generateContent(
    Request $request,
    HuggingFaceService $huggingFace
): Response {
    $title = $request->request->get('title');
    
    if (empty($title)) {
        return $this->json(['error' => 'Title is required'], 400);
    }

    $prompt = "Write a detailed blog post about: $title\n\n";
    $generatedContent = $huggingFace->generateContent($prompt);

    return $this->json([
        'content' => $generatedContent
    ]);
}
   
}