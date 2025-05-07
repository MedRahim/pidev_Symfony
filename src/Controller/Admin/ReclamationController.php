<?php

// src/Controller/Admin/ReclamationController.php
namespace App\Controller\Admin;

use App\Entity\Reclamation;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Knp\Snappy\Pdf;

#[Route('/admin/reclamations')]
class ReclamationController extends AbstractController
{
    #[Route('/', name: 'admin_reclamation_index')]
    public function index(Request $request, ReclamationRepository $repo): Response
    {
        $state = $this->getStateFromRequest($request);
        $reclamations = $this->getFilteredReclamations($repo, $state);
        $stats = $this->getStatistics($repo);

        return $this->render('Admin/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'currentState' => $state,
            'stats' => $stats
        ]);
    }

    #[Route('/test', name: 'aaa')]
    public function index2(): Response
    {
        return $this->render('BackOffice/example.html.twig'
           
        );
    }

    #[Route('/stats/monthly', name: 'admin_reclamation_monthly_stats')]
    public function getMonthlyStats(ReclamationRepository $repo, Request $request): JsonResponse
    {
        $year = $request->query->get('year');
        $month = $request->query->get('month');
        
        $stats = $repo->getMonthlyStats(
            $year ? (int)$year : null,
            $month ? (int)$month : null
        );
        
        return $this->json($stats);
    }

    #[Route('/stats/types', name: 'admin_reclamation_type_stats')]
    public function getTypeStats(ReclamationRepository $repo, Request $request): JsonResponse
    {
        $year = $request->query->get('year');
        $month = $request->query->get('month');
        
        $stats = $repo->getTypeStats(
            $year ? (int)$year : null,
            $month ? (int)$month : null
        );
        
        return $this->json($stats);
    }

    #[Route('/statistics', name: 'admin_reclamation_statistics')]
    public function statistics(ReclamationRepository $repo): Response
    {
        $reclamations = $repo->findAll();
        return $this->render('admin/reclamation/statistics.html.twig', [
            'reclamations' => $reclamations
        ]);
    }

    #[Route('/statistics/pdf', name: 'admin_reclamation_statistics_pdf')]
    public function statisticsPdf(Request $request, ReclamationRepository $repo, \Knp\Snappy\Pdf $knpSnappyPdf): Response
    {
        $reclamations = $repo->findAll();
        $monthlyChartImg = $request->request->get('monthly_chart_img');
        $typeChartImg = $request->request->get('type_chart_img');
        $startDate = (new \DateTime('first day of this month'))->setTime(0,0,0);
        $endDate = (new \DateTime('last day of this month'))->setTime(23,59,59);
        $html = $this->renderView('admin/reclamation/statistics_pdf.html.twig', [
            'reclamations' => $reclamations,
            'monthly_chart_img' => $monthlyChartImg,
            'type_chart_img' => $typeChartImg,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'prev_period_count' => null,
            'pending_change' => null,
            'resolved_change' => null,
            'resolution_rate_change' => null,
            'top_categories_percentage' => null,
            'top_category' => null,
            'top_category_percentage' => null,
            'resolution_time_chart_img' => null,
            'avg_resolution_time' => null,
            'resolution_time_75p' => null,
            'location_chart_img' => null,
            'hotspot_area' => null,
            'hotspot_percentage' => null,
            'top_issues' => [],
            'recent_reclamations' => [],
        ]);
        return new Response(
            $knpSnappyPdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="statistiques_charts.pdf"'
            ]
        );
    }

    private function getStatistics(ReclamationRepository $repo): array
    {
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        $monthlyStats = $repo->getMonthlyStats($currentYear, $currentMonth);
        $typeStats = $repo->getTypeStats($currentYear, $currentMonth);

        return [
            'total' => $repo->count([]),
            'pending' => $repo->count(['state' => false]),
            'resolved' => $repo->count(['state' => true]),
            'resolution_rate' => $this->calculateResolutionRate($repo),
            'monthly_stats' => $monthlyStats,
            'type_stats' => $typeStats
        ];
    }

    private function calculateResolutionRate(ReclamationRepository $repo): float
    {
        $total = $repo->count([]);
        if ($total === 0) {
            return 0;
        }
        $resolved = $repo->count(['state' => true]);
        return ($resolved / $total) * 100;
    }

    private function getStateFromRequest(Request $request): ?bool
    {
        $state = $request->query->get('state');
        if ($state === 'pending') {
            return false;
        }
        if ($state === 'resolved') {
            return true;
        }
        return null;
    }

    private function getFilteredReclamations(ReclamationRepository $repo, ?bool $state): array
    {
        if ($state === null) {
            return $repo->findAll();
        }
        return $repo->findBy(['state' => $state]);
    }

    #[Route('/{id}/toggle-state', name: 'admin_reclamation_toggle_state', methods: ['POST'])]
    public function toggleState(Reclamation $reclamation, EntityManagerInterface $em, MailerInterface $mailer): JsonResponse
    {
        try {
            // Toggle the state
            $newState = !$reclamation->isState();
            $reclamation->setState($newState);
            $em->flush();

            // If marking as resolved, send email
            if ($newState && $reclamation->getEmail()) {
                $emailBody = "Bonjour,\n\n" .
                    "Votre réclamation a été résolue. Voici un rappel de ses détails :\n" .
                    "Numéro : " . $reclamation->getId() . "\n" .
                    "Type : " . $reclamation->getType() . "\n" .
                    "Description : " . $reclamation->getDescription() . "\n" .
                    "Date : " . $reclamation->getDatee() . "\n\n" .
                    "Si ce n'est pas le cas, veuillez nous contacter.\n\nMerci de votre confiance.";

                $email = (new Email())
                    ->from('Smart City Support <dridi.mohammed01@gmail.com>')
                    ->to($reclamation->getEmail())
                    ->subject('Votre réclamation a été résolue')
                    ->text($emailBody);

                try {
                    $mailer->send($email);
                } catch (\Exception $e) {
                    // Log email error but don't fail the request
                    error_log('Failed to send email: ' . $e->getMessage());
                }
            }

            return $this->json([
                'success' => true,
                'newState' => $newState,
                'message' => $newState ? 'Reclamation marked as resolved' : 'Reclamation marked as pending'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to update reclamation state: ' . $e->getMessage()
            ], 500);
        }
    }
}
