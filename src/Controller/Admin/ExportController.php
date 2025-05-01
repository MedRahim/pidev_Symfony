<?php
// src/Controller/Admin/ExportController.php
namespace App\Controller\Admin;

use App\Entity\Reservations;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    #[Route('/admin/reservations/export.csv', name: 'export_reservations_csv')]
    public function exportCsv(ManagerRegistry $doctrine): StreamedResponse
    {
        $repository = $doctrine->getRepository(Reservations::class);

        $response = new StreamedResponse(function() use ($repository) {
            $handle = fopen('php://output', 'w+');
            // en-têtes CSV
            fputcsv($handle, ['ID', 'Client', 'Date', 'Montant']);

            foreach ($repository->findAll() as $res) {
                fputcsv($handle, [
                    $res->getId(),
                    $res->getClient()->getFullName(),
                    $res->getDate()->format('Y-m-d'),
                    $res->getAmount(),
                ]);
            }

            fclose($handle);
        });

        // en-têtes de la réponse HTTP
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="reservations.csv"');

        return $response;
    }
}
