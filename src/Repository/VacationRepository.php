<?php

namespace App\Repository;

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
