<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/backoffice/products', name: 'products_page', methods: ['GET', 'POST'])]
    public function index(
        ProductRepository $productRepository, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        FormFactoryInterface $formFactory,
        PaginatorInterface $paginator
    ): Response
    {
        $query = $productRepository->createQueryBuilder('p')->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        $product = new Product();
        $form = $formFactory->create(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/products',
                    $newFilename
                );
                $product->setImagePath('/uploads/products/'.$newFilename);
            } else {
                $product->setImagePath(null);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new Response('Product added successfully', Response::HTTP_OK);
            }

            return $this->redirectToRoute('products_page');
        }

        return $this->render('BackOffice/products.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request); // Handle the form submission

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload (optional)
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads',
                    $newFilename
                );
                $product->setImagePath($newFilename);
            }

            // Persist to database
            $entityManager->persist($product);
            $entityManager->flush();

            // Redirect to avoid duplicate submissions
            return $this->redirectToRoute('products_page');
        }

        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/listing', name: 'app_product_listing', methods: ['GET'])]
    public function listing(ProductRepository $productRepository, Request $request): Response
    {
        $searchName = $request->query->get('name');
        $minPrice = $request->query->get('min_price');
        $maxPrice = $request->query->get('max_price');

        $minPrice = $minPrice !== null && $minPrice !== '' ? (float)$minPrice : null;
        $maxPrice = $maxPrice !== null && $maxPrice !== '' ? (float)$maxPrice : null;

        $products = $productRepository->findByNameAndPriceRange($searchName, $minPrice, $maxPrice);

        return $this->render('FrontOffice/market.html.twig', [
            'products' => $products,
            'searchName' => $searchName,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    #[Route('/market', name: 'app_product_market', methods: ['GET'])]
    public function listProducts(Request $request): Response
    {
        $name = $request->query->get('name', '');
        $priceRange = $request->query->get('price-range', '');

        $queryBuilder = $this->getDoctrine()->getRepository(Product::class)->createQueryBuilder('p');

        if ($name) {
            $queryBuilder->andWhere('p.name LIKE :name')
                         ->setParameter('name', '%' . $name . '%');
        }

        if ($priceRange) {
            [$minPrice, $maxPrice] = explode('-', $priceRange);
            $queryBuilder->andWhere('p.price BETWEEN :minPrice AND :maxPrice')
                         ->setParameter('minPrice', $minPrice)
                         ->setParameter('maxPrice', $maxPrice);
        }

        $products = $queryBuilder->getQuery()->getResult();

        return $this->render('FrontOffice/market.html.twig', [
            'products' => $products,
            'searchName' => $name,
            'selectedPriceRange' => $priceRange,
        ]);
    }

    #[Route('/edit-modal/{id}', name: 'app_product_edit_modal', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function editModal(Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);

        // Render only the <form> markup (no layout, no menus, etc)
        return $this->render('product/_edit_modal_form.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        Product $product,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ProductType::class, $product, [
            'attr' => ['enctype' => 'multipart/form-data']
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file upload if any
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $safeName = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
                $filename = $safeName . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $filename);
                $product->setImagePath('uploads/images/' . $filename);
            }

            // Flush changes to the database
            $em->flush();

            // Return updated product list HTML for AJAX
            if ($request->isXmlHttpRequest()) {
                $products = $em->getRepository(Product::class)->findAll();
                return $this->json([
                    'success' => true,
                    'html' => $this->renderView('BackOffice/_product_list.html.twig', [
                        'products' => $products,
                    ]),
                ]);
            }

            // Redirect or dynamically render the list of products
            $products = $em->getRepository(Product::class)->findAll();
            return $this->render('BackOffice/_product_list.html.twig', [
                'products' => $products,
            ]);
        }

        // Render the form for GET requests
        return $this->render('product/_edit_modal_form.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                $products = $entityManager->getRepository(Product::class)->findAll();
                return $this->json([
                    'success' => true,
                    'html' => $this->renderView('BackOffice/_product_list.html.twig', [
                        'products' => $products,
                    ]),
                ]);
            }
        }

        return $this->redirectToRoute('products_page', [], Response::HTTP_SEE_OTHER);
    }
}
