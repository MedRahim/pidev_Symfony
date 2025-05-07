<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository
    ) {
    }

    #[Route('/api/invoices/stock', name: 'api_invoices_stock', methods: ['GET'])]
    public function generateInvoice(): Response
    {
        // Get products and calculate totals
        $products = $this->productRepository->findAll();
        $total = array_sum(array_map(fn($p) => $p->getPrice() * $p->getStock(), $products));
        $facture = $total * 1.19; // Including 19% tax

        // Generate the HTML content
        $html = $this->renderView('invoice/template.html.twig', [
            'products' => $products,
            'total' => $total,
            'facture' => $facture
        ]);

        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        // Create and configure Dompdf instance
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Generate PDF filename
        $filename = 'MdinTech_Stock_Invoice_' . date('Y-m-d_H-i-s') . '.pdf';

        // Return the PDF as a response
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