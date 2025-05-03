<?php

// src/Controller/ChartController.php
namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartController extends AbstractController
{
    #[Route('/charts/users', name: 'user_charts')]
    public function userStats(UserRepository $userRepository, ChartBuilderInterface $chartBuilder): Response
    {
        // Example: Count active vs inactive users
        $activeUsers = $userRepository->count(['isActive' => true]);
        $inactiveUsers = $userRepository->count(['isActive' => false]);

        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => ['Active Users', 'Inactive Users'],
            'datasets' => [[
                'label' => 'User Status',
                'backgroundColor' => ['#36A2EB', '#FF6384'],
                'data' => [$activeUsers, $inactiveUsers],
            ]],
        ]);
        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => false,
        ]);

        return $this->render('chart/user_stats.html.twig', [
            'chart' => $chart,
        ]);
    }
}
