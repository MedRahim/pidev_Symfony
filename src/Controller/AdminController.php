<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/admin', name: 'app_admin_dashboard')]
    public function dashboard(
        UserRepository $userRepository,
        ChartBuilderInterface $chartBuilder
    ): Response {
        // Get data from repository
        $registrationData = $userRepository->getRegistrationTimeline();
        $rolesData = $userRepository->countUsersByRole();

        // Registration Timeline Chart
        $registrationChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $registrationChart->setData([
            'labels' => array_map(fn($d) => date('M Y', strtotime($d['month'].'-01')), $registrationData),
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'backgroundColor' => 'rgb(78, 115, 223)',
                    'borderColor' => 'rgb(78, 115, 223)',
                    'data' => array_column($registrationData, 'count'),
                ],
            ],
        ]);

        // User Roles Chart
        $rolesChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $rolesChart->setData([
            'labels' => array_map(fn($r) => $this->formatRoleName($r['roles'][0]), $rolesData),
            'datasets' => [
                [
                    'label' => 'Users by Role',
                    'backgroundColor' => ['#4e73df', '#1cc88a', '#36b9cc'],
                    'data' => array_column($rolesData, 'count'),
                ],
            ],
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'registrationChart' => $registrationChart,
            'rolesChart' => $rolesChart,
            'stats' => [
                'total_users' => $userRepository->countTotalUsers(),
                'active_users' => $userRepository->countActiveUsers(),
                'verified_users' => $userRepository->countVerifiedUsers(),
            ]
        ]);
    }

    // src/Controller/AdminController.php
    private function formatRoleName(string $role): string
    {
        return match ($role) {
            'ROLE_SUPER_ADMIN' => 'Super Administrator',
            'ROLE_ADMIN' => 'Administrator',
            'ROLE_MODERATOR' => 'Moderator',
            'ROLE_EDITOR' => 'Editor',
            'ROLE_USER' => 'Standard User',
            default => ucfirst(strtolower(str_replace('ROLE_', '', $role)) . ' User')};
    }
}
