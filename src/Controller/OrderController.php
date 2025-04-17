<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product; // Add this import
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('/BackOffice/orders', name: 'orders_page', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll(); // Fetch all orders from the database

        return $this->render('BackOffice/orders.html.twig', [
            'orders' => $orders, // Pass the orders variable to the template
        ]);
    }

    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_order_show', methods: ['GET'])]
    public function show(int $id, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($id);

        if (!$order) {
            throw $this->createNotFoundException(sprintf('Order with ID %d not found.', $id));
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();

            return $this->redirectToRoute('orders_page', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('orders_page', [], Response::HTTP_FORBIDDEN);
    }

    #[Route('/order/create', name: 'order_create', methods: ['POST'])]
    public function createOrder(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId']) || !isset($data['_csrf_token'])) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid request data'], 400);
        }

        $csrfToken = new CsrfToken('order', $data['_csrf_token']);
        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return new JsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
        }

        $product = $entityManager->getRepository(Product::class)->find($data['productId']);
        if (!$product) {
            return new JsonResponse(['success' => false, 'error' => 'Product not found'], 404);
        }

        $order = new Order();
        $order->setProduct($product);
        $order->setStatus('not approved');
        $order->setCreatedAt(new \DateTime());

        $entityManager->persist($order);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Order created successfully']);
    }

    #[Route('/listing', name: 'app_order_listing', methods: ['GET'])]
    public function listing(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('FrontOffice/market.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{id}/confirm', name: 'app_order_confirm', methods: ['POST'])]
    public function confirm(Request $request, Order $order, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('confirm' . $order->getId(), $request->request->get('_token'))) {
            $order->setStatus('confirmed');
            $entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Order confirmed successfully']);
        }

        return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
    }

    #[Route('/{id}/details', name: 'app_order_details', methods: ['GET'])]
    public function details(Order $order): Response
    {
        return $this->render('BackOffice/order_details_modal.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/pannier', name: 'app_order_pannier', methods: ['GET'])]
    public function pannier(OrderRepository $orderRepository): Response
    {
        $confirmedOrders = $orderRepository->findBy(['status' => 'confirmed']); // Fetch orders with status 'confirmed'

        return $this->render('FrontOffice/pannier.html.twig', [
            'orders' => $confirmedOrders, // Pass confirmed orders to the template
        ]);
    }

    #[Route('/pannier/pay', name: 'app_order_pannier_pay', methods: ['POST'])]
    public function pay(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $orderIds = $request->request->all('order_ids'); // Use all() to retrieve an array of order IDs

        if (empty($orderIds)) {
            $this->addFlash('error', 'No orders selected for payment.');
            return $this->redirectToRoute('app_order_pannier');
        }

        $orders = $orderRepository->findBy(['id' => $orderIds, 'status' => 'confirmed']);

        if (empty($orders)) {
            $this->addFlash('error', 'No valid orders found for payment.');
            return $this->redirectToRoute('app_order_pannier');
        }

        // Simulate payment processing
        foreach ($orders as $order) {
            $order->setStatus('paid');
        }

        $entityManager->flush();

        $this->addFlash('success', 'Payment successful for selected orders.');
        return $this->redirectToRoute('app_order_pannier');
    }
}
