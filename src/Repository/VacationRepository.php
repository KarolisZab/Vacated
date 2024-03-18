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

    public function findAllConfirmedVacationsForPeriod(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        bool $isRejected
    ): array {
        return $this->createQueryBuilder('v')
            ->where('v.dateFrom <= :endDate')
            ->andWhere('v.dateTo >= :startDate')
            ->andWhere('v.isRejected = :rejected')
            ->andWhere('v.isConfirmed = :confirmed')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('rejected', $isRejected)
            ->setParameter('confirmed', true)
            ->orderBy('v.dateFrom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllRequestedVacationsForPeriodByUser(
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        User $user
    ): array {
        return $this->createQueryBuilder('v')
            ->where('v.dateFrom <= :endDate')
            ->andWhere('v.dateTo >= :startDate')
            ->andWhere('v.requestedBy = :user')
            ->andWhere('v.isConfirmed = :confirmed')
            ->andWhere('v.isRejected = :rejected')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('user', $user)
            ->setParameter('confirmed', false)
            ->setParameter('rejected', false)
            ->orderBy('v.dateFrom', 'ASC')
            ->getQuery()
            ->getResult();
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
