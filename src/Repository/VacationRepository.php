<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vacation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vacation>
 *
 * @method Vacation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vacation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vacation[]    findAll()
 * @method Vacation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VacationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vacation::class);
    }

    private function filterOverlappingVacationsForPeriod(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ) {
        return $this->createQueryBuilder('v')
            ->where('v.dateFrom <= :endDate')
            ->andWhere('v.dateTo >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('v.dateFrom', 'ASC');
    }

    public function getConfirmedVacationsForPeriod(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
    ): array {

        $query = $this->filterOverlappingVacationsForPeriod($startDate, $endDate);

        return $query
            ->andWhere('v.isConfirmed = TRUE')
            ->getQuery()
            ->getResult();
    }

    public function getRequestedVacationsForPeriodByUser(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        string $userId
    ): array {
        $query = $this->filterOverlappingVacationsForPeriod($startDate, $endDate);

        return $query
            ->andWhere('v.requestedBy = :userId')
            ->andWhere('v.isConfirmed = FALSE')
            ->andWhere('v.isRejected = FALSE')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    public function getVacations(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('v')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function getVacationsCount(?string $filter = null): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findOverlappingUserVacations(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        User $user
    ): array {
        return $this->createQueryBuilder('v')
            ->where('v.dateFrom <= :endDate')
            ->andWhere('v.dateTo >= :startDate')
            ->andWhere('v.requestedBy = :user')
            ->andWhere('v.isRejected = FALSE')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function getAllCurrentUserFilteredVacations(User $user, ?string $vacationType): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.requestedBy = :user')
            ->setParameter('user', $user);
        $now = new \DateTimeImmutable();

        if ($vacationType === 'requested') {
            $qb->andWhere('v.isConfirmed = FALSE')
                ->andWhere('v.isRejected = FALSE')
                ->orderBy('v.dateFrom', 'ASC');
        }

        if ($vacationType === 'confirmed') {
            $qb->andWhere('v.isConfirmed = TRUE')
                ->orderBy('v.dateFrom', 'ASC');
        }

        if ($vacationType === 'rejected') {
            $qb->andWhere('v.isRejected = TRUE')
                ->orderBy('v.dateFrom', 'ASC');
        }

        if ($vacationType === 'upcoming') {
            $qb->andWhere('v.dateTo > :now')
                ->andWhere('v.isConfirmed = TRUE')
                ->setParameter('now', $now)
                ->orderBy('v.dateFrom', 'ASC');
        }

        if ($vacationType === '' || $vacationType === null) {
            return $qb->getQuery()->getResult();
        }

        return [];
    }

    public function getFilteredVacations(?string $vacationType): array
    {
        $qb = $this->createQueryBuilder('v');
        $now = new \DateTimeImmutable();

        if ($vacationType === 'requested') {
            $qb->where('v.isConfirmed = FALSE')
               ->andWhere('v.isRejected = FALSE')
               ->orderBy('v.requestedAt', 'ASC');
        }

        if ($vacationType === 'confirmed') {
            $qb->where('v.isConfirmed = TRUE')
               ->orderBy('v.reviewedAt', 'ASC');
        }

        if ($vacationType === 'rejected') {
            $qb->where('v.isRejected = TRUE')
                ->orderBy('v.reviewedAt', 'ASC');
        }

        if ($vacationType === 'upcoming') {
            $qb->where('v.dateTo > :now')
                ->andWhere('v.isConfirmed = TRUE')
                ->setParameter('now', $now)
                ->orderBy('v.dateFrom', 'ASC');
        }

        if ($vacationType === '' || $vacationType === null) {
            // return [];
            return $qb->getQuery()->getResult();
        }

        // return $qb->getQuery()->getResult();
        return [];
    }

//    /**
//     * @return Vacation[] Returns an array of Vacation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vacation
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
