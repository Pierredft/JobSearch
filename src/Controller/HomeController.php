<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ApplicationRepository $applicationRepository): Response
    {
        // Get statistics
        $totalApplications = $applicationRepository->count([]);
        $statsByStatus = $applicationRepository->getStatsByStatus();
        $monthlyStats = $applicationRepository->getMonthlyStats(6);
        $dailyStats = $applicationRepository->getDailyStats(30);
        $monthlyStatsByStatus = $applicationRepository->getMonthlyStatsByStatus(6);
        $responseRate = $applicationRepository->getResponseRate();
        $successRate = $applicationRepository->getSuccessRate();
        $recentApplications = $applicationRepository->findRecent(5);
        $statsBySector = $applicationRepository->getStatsBySector();

        // Prepare data for monthly chart
        $monthlyLabels = [];
        $monthlyData = [];
        foreach ($monthlyStats as $stat) {
            $date = new \DateTime("{$stat['year']}-{$stat['month']}-01");
            $monthlyLabels[] = $date->format('M Y');
            $monthlyData[] = $stat['total'];
        }

        // Prepare data for daily chart
        $dailyLabels = [];
        $dailyData = [];
        // Fill in missing days
        $today = new \DateTime();
        $period = new \DatePeriod(
            (new \DateTime())->modify('-29 days'),
            new \DateInterval('P1D'),
            $today->modify('+1 day') // Include today
        );

        $statsByDate = [];
        foreach ($dailyStats as $stat) {
            $statsByDate[$stat['date']] = $stat['total'];
        }

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $dailyLabels[] = $date->format('d/m');
            $dailyData[] = $statsByDate[$dateString] ?? 0;
        }

        // Prepare data for status chart (monthly breakdown)
        $statusData = [
            'sent' => [],
            'no_response' => [],
            'negative_response' => [],
            'positive_response' => [],
        ];

        // Create a map of all months
        $allMonths = [];
        foreach ($monthlyStats as $stat) {
            $key = "{$stat['year']}-{$stat['month']}";
            $allMonths[$key] = 0;
        }

        // Fill status data
        foreach ($monthlyStatsByStatus as $stat) {
            $key = "{$stat['year']}-{$stat['month']}";
            $statusValue = $stat['status']->value;
            if (isset($statusData[$statusValue])) {
                $statusData[$statusValue][$key] = $stat['count'];
            }
        }

        // Ensure all months have data for each status
        foreach ($statusData as &$data) {
            foreach (array_keys($allMonths) as $month) {
                if (!isset($data[$month])) {
                    $data[$month] = 0;
                }
            }
            ksort($data);
            $data = array_values($data);
        }

        return $this->render('home/index.html.twig', [
            'totalApplications' => $totalApplications,
            'statsByStatus' => $statsByStatus,
            'responseRate' => round($responseRate, 1),
            'successRate' => round($successRate, 1),
            'recentApplications' => $recentApplications,
            'monthlyLabels' => $monthlyLabels,
            'monthlyData' => $monthlyData,
            'dailyLabels' => $dailyLabels,
            'dailyData' => $dailyData,
            'statusData' => $statusData,
            'statsBySector' => $statsBySector,
            'sectorLabels' => array_keys($statsBySector),
            'sectorData' => array_values($statsBySector),
        ]);
    }
}
