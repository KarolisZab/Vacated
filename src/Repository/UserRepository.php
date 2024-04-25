<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countAllUsers(?string $filter = null): int
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u)');

        if (null !== $filter) {
            $qb
                ->where('u.firstName LIKE :filter OR 
                    u.lastName LIKE :filter OR 
                    u.email LIKE :filter OR 
                    u.phoneNumber LIKE :filter')
                ->setParameter('filter', '%' . $filter . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getUsers(int $limit = 10, int $offset = 0, ?string $filter = null): array
    {
        $qb = $this->createQueryBuilder('u');
        if (null !== $filter) {
            $qb
                ->where('u.firstName LIKE :filter OR 
                    u.lastName LIKE :filter OR 
                    u.email LIKE :filter OR 
                    u.phoneNumber LIKE :filter')
                ->setParameter('filter', '%' . $filter . '%');
        }
        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function getEmployeesCount(?string $filter = null): int
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.isAdmin = FALSE')
            ->getQuery()
            ->getSingleScalarResult();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
