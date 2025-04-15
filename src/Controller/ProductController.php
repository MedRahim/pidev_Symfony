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

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route('/backoffice/products', name: 'products_page', methods: ['GET', 'POST'])]
    public function index(ProductRepository $productRepository, Request $request, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory): Response
    {
        $products = $productRepository->findAll();

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
                $product->setImagePath(null); // Explicitly set to null if no file is uploaded
            }

            $entityManager->persist($product);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new Response('Product added successfully', Response::HTTP_OK);
            }

            return $this->redirectToRoute('products_page');
        }

        return $this->render('BackOffice/products.html.twig', [
            'products' => $products,
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

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/products',
                    $newFilename
                );
                $product->setImagePath('/uploads/products/'.$newFilename);
            }

            $entityManager->flush(); // Save changes to the existing product

            return new Response('Product updated successfully', Response::HTTP_OK);
        }

        // Render the edit form for AJAX requests
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            return $this->render('BackOffice/edit_product_modal.html.twig', [
                'form' => $form->createView(),
                'product' => $product,
            ]);
        }

        return $this->redirectToRoute('products_page');
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('products_page', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/listing', name: 'app_product_listing', methods: ['GET'])]
    public function listing(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll(); // Fetch all products from the database

        return $this->render('FrontOffice/listing.html.twig', [
            'products' => $products, // Pass the products to the template
        ]);
    }
}
