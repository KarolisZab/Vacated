<?php

namespace App\Repository;

use App\Entity\ReservedDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReservedDay>
 *
 * @method ReservedDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReservedDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReservedDay[]    findAll()
 * @method ReservedDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservedDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReservedDay::class);
    }

    public function findReservedDaysInPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.dateFrom <= :to')
            ->andWhere('r.dateTo >= :from')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('r.dateFrom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPaginatedReservedDays(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('r')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    // public function findReservedDaysInPeriod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    // {
    //     return $this->createQueryBuilder('r')
    //         ->where('r.dateFrom <= :from')
    //         ->andWhere('r.dateTo >= :from')
    //         ->orWhere('r.dateFrom <= :to')
    //         ->andWhere('r.dateTo >= :to')
    //         ->setParameter('from', $from)
    //         ->setParameter('to', $to)
    //         ->getQuery()
    //         ->getResult();
    // }

//    /**
//     * @return ReservedDay[] Returns an array of ReservedDay objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReservedDay
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
