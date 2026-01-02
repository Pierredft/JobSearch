<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Application;
use App\Enum\ApplicationStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Application>
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * Get applications grouped by month.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonthlyStats(int $monthsBack = 6): array
    {
        $startDate = new \DateTime("-{$monthsBack} months");
        $startDate->modify('first day of this month');

        $qb = $this->createQueryBuilder('a')
            ->select('YEAR(a.applicationDate) as year, MONTH(a.applicationDate) as month, COUNT(a.id) as total')
            ->where('a.applicationDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->groupBy('year, month')
            ->orderBy('year, month', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get statistics by status.
     *
     * @return array<string, array{status: ApplicationStatus, count: int}>
     */
    public function getStatsByStatus(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->groupBy('a.status');

        $results = $qb->getQuery()->getResult();

        $stats = [];
        foreach ($results as $result) {
            $stats[$result['status']->value] = [
                'status' => $result['status'],
                'count' => $result['count'],
            ];
        }

        return $stats;
    }

    /**
     * Get monthly stats by status.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMonthlyStatsByStatus(int $monthsBack = 6): array
    {
        $startDate = new \DateTime("-{$monthsBack} months");
        $startDate->modify('first day of this month');

        $qb = $this->createQueryBuilder('a')
            ->select('YEAR(a.applicationDate) as year, MONTH(a.applicationDate) as month, a.status, COUNT(a.id) as count')
            ->where('a.applicationDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->groupBy('year, month, a.status')
            ->orderBy('year, month', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get recent applications.
     *
     * @return Application[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.applicationDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calculate response rate.
     */
    public function getResponseRate(): float
    {
        $total = $this->count([]);
        if (0 === $total) {
            return 0;
        }

        $withResponse = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.status != :sent AND a.status != :noResponse')
            ->setParameter('sent', ApplicationStatus::SENT)
            ->setParameter('noResponse', ApplicationStatus::NO_RESPONSE)
            ->getQuery()
            ->getSingleScalarResult();

        return ($withResponse / $total) * 100;
    }

    /**
     * Calculate success rate.
     */
    public function getSuccessRate(): float
    {
        $total = $this->count([]);
        if (0 === $total) {
            return 0;
        }

        $positive = $this->count(['status' => ApplicationStatus::POSITIVE_RESPONSE]);

        return ($positive / $total) * 100;
    }
}
