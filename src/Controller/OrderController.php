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
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('/BackOffice/orders', name: 'orders_page', methods: ['GET'])]
    public function index(OrderRepository $orderRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $orderRepository->createQueryBuilder('o')
            ->orderBy('o.date', 'DESC')
            ->getQuery();
        
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10 // Items per page
        );

        return $this->render('BackOffice/orders.html.twig', [
            'pagination' => $pagination,
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
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Order deleted successfully']);
        }

        return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
    }

    #[Route('/order/create', name: 'order_create', methods: ['POST'])]
    public function createOrder(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['productId']) || !isset($data['_csrf_token']) || !isset($data['quantity'])) {
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

        try {
            // Create the order
            $order = new Order();
            $order->setProduct($product);
            $order->setStatus('not approved');
            $order->setCreatedAt(new \DateTime());

            // Calculate total price
            $quantity = (int)$data['quantity'];
            $unitPrice = $product->getPrice();
            $totalPrice = $unitPrice * $quantity;

            // Create the order item
            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPriceTotal($totalPrice);
            $orderItem->setOrder($order);

            $entityManager->persist($order);
            $entityManager->persist($orderItem);
            $entityManager->flush();

            return new JsonResponse([
                'success' => true, 
                'message' => 'Order created successfully',
                'data' => [
                    'unitPrice' => $unitPrice,
                    'quantity' => $quantity,
                    'totalPrice' => $totalPrice
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
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
        $data = json_decode($request->getContent(), true);
        
        if (!$this->isCsrfTokenValid('confirm' . $order->getId(), $data['_token'])) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], Response::HTTP_FORBIDDEN);
        }

        $order->setStatus('confirmed');
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Order confirmed successfully']);
    }

    #[Route('/{id}/details', name: 'app_order_details', methods: ['GET'])]
    public function details(Order $order): JsonResponse
    {
        return $this->json([
            'id' => $order->getId(),
            'date' => $order->getDate()->format('Y-m-d H:i:s'),
            'status' => $order->getStatus(),
            'product' => [
                'name' => $order->getProduct()->getName(),
                'price' => $order->getProduct()->getPrice(),
            ],
            'quantity' => $order->getOrderItems()->first() ? $order->getOrderItems()->first()->getQuantity() : 1,
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
    public function pay(Request $request, OrderRepository $orderRepository): Response
    {
        $stripePublicKey = $this->getParameter('stripe_public_key'); // Ensure this line retrieves the parameter correctly

        $orderIds = $request->request->all('order_ids');

        if (empty($orderIds)) {
            $this->addFlash('error', 'No orders selected for payment.');
            return $this->redirectToRoute('app_order_pannier');
        }

        return $this->render('FrontOffice/payment_form.html.twig', [
            'orderIds' => $orderIds,
            'stripe_public_key' => $stripePublicKey,
        ]);
    }

    #[Route('/create-payment-intent', name: 'app_order_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request, OrderRepository $orderRepository): JsonResponse
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        try {
            $orderIds = json_decode($request->getContent(), true)['orderIds'];
            $orders = $orderRepository->findBy(['id' => $orderIds]);
            $amount = array_sum(array_map(fn($order) => $order->getTotalPrice(), $orders)) * 100;

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                'metadata' => [
                    'order_ids' => implode(',', $orderIds)
                ]
            ]);

            return $this->json(['clientSecret' => $paymentIntent->client_secret]);
        } catch (ApiErrorException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/pannier/payment-success', name: 'app_order_payment_success', methods: ['GET'])]
    public function paymentSuccess(EntityManagerInterface $entityManager, OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findBy(['status' => 'confirmed']);

        foreach ($orders as $order) {
            $order->setStatus('paid');
        }

        $entityManager->flush();

        $this->addFlash('success', 'Payment processed successfully!');
        return $this->redirectToRoute('app_order_pannier');
    }

    #[Route('/delete-multiple', name: 'app_order_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $csrfToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_multiple', $csrfToken)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_order_pannier');
        }

        $orderIds = explode(',', $request->request->get('order_ids'));
       
        if (empty($orderIds)) {
            $this->addFlash('error', 'No orders selected for deletion.');
            return $this->redirectToRoute('app_order_pannier');
        }

        $orders = $orderRepository->findBy(['id' => $orderIds]);
       
        foreach ($orders as $order) {
            $entityManager->remove($order);
        }
       
        $entityManager->flush();
       
        $this->addFlash('success', sprintf('Deleted %d orders successfully.', count($orders)));
        return $this->redirectToRoute('app_order_pannier');
    }
}


