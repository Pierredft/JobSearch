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
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT YEAR(application_date) as year, MONTH(application_date) as month, COUNT(id) as total
            FROM application
            WHERE application_date >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
            GROUP BY year, month
            ORDER BY year ASC, month ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['months' => $monthsBack]);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Get statistics by status.
     *
     * @return array<string|int, array{status: ApplicationStatus, count: int}>
     */
    public function getStatsByStatus(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->groupBy('a.status');

        $results = $qb->getQuery()->getResult();

        $stats = [];
        foreach ($results as $result) {
            $status = $result['status'];
            $stats[$status->value] = [
                'status' => $status,
                'count' => (int) $result['count'],
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
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT YEAR(application_date) as year, MONTH(application_date) as month, status, COUNT(id) as count
            FROM application
            WHERE application_date >= DATE_SUB(CURRENT_DATE, INTERVAL :months MONTH)
            GROUP BY year, month, status
            ORDER BY year ASC, month ASC
        ';

        $resultSet = $conn->executeQuery($sql, ['months' => $monthsBack]);
        $results = $resultSet->fetchAllAssociative();

        // On remet les statuts dans leur format Enum pour le controlleur
        foreach ($results as &$result) {
            $result['status'] = ApplicationStatus::from($result['status']);
        }

        return $results;
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

        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT COUNT(id) FROM application WHERE status NOT IN (:sent, :no_response)';
        $count = $conn->fetchOne($sql, [
            'sent' => ApplicationStatus::SENT->value,
            'no_response' => ApplicationStatus::NO_RESPONSE->value,
        ]);

        return ($count / $total) * 100;
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

        $positiveCount = $this->count(['status' => ApplicationStatus::POSITIVE_RESPONSE]);

        return ($positiveCount / $total) * 100;
    }
}
