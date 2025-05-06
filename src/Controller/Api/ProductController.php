<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/api/products/low-stock', name: 'api_products_low_stock', methods: ['GET'])]
    public function lowStock(): JsonResponse
    {
        $products = $this->productRepository->findLowStock();
        
        return $this->json($products, 200, [], [
            'groups' => ['product:list']
        ]);
    }

    #[Route('/api/products/{id}/add-stock', name: 'api_products_add_stock', methods: ['POST'])]
    public function addStock(int $id, Request $request): JsonResponse
    {
        $amount = (int) $request->request->get('amount', 0);
        if ($amount <= 0) {
            return $this->json(['error' => 'Amount must be positive'], 400);
        }

        $product = $this->productRepository->find($id);
        if (!$product) {
            return $this->json(['error' => 'Product not found'], 404);
        }

        $product->setStock($product->getStock() + $amount);
        $this->entityManager->flush();

        return $this->json($product, 200, [], [
            'groups' => ['product:list']
        ]);
    }
} 