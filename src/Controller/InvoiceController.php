<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {
    }

    #[Route('/invoices/order/{orderIds}', name: 'app_invoices_order', methods: ['GET'])]
    public function generateInvoice(string $orderIds): Response
    {
        // Split the order IDs (e.g., "1,2,3" into an array [1, 2, 3])
        $orderIdArray = explode(',', $orderIds);
        $orders = $this->orderRepository->findBy(['id' => $orderIdArray]);

        // Check if orders exist
        if (empty($orders)) {
            throw $this->createNotFoundException('No valid orders found');
        }

        // Calculate subtotal from order items
        $total = 0;
        foreach ($orders as $order) {
            foreach ($order->getOrderItems() as $item) {
                $total += $item->getPriceTotal(); // Assumes OrderItem has getPriceTotal()
            }
        }
        $facture = $total * 1.19; // Add 19% tax

        // Generate a unique invoice number for display (not stored)
        $invoiceNumber = uniqid('INV-');

        // Render the HTML template with order data
        $html = $this->renderView('invoice/order_invoice.html.twig', [
            'invoiceNumber' => $invoiceNumber,
            'orders' => $orders,
            'total' => $total,
            'facture' => $facture,
            'date' => new \DateTime(), // Current date
        ]);

        // Set up Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Generate the PDF
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Create a filename for the PDF
        $filename = 'Order_Invoice_' . $invoiceNumber . '_' . date('Y-m-d_H-i-s') . '.pdf';

        // Return the PDF as a downloadable response
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
}