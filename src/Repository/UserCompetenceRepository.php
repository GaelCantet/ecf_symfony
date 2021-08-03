<?php

namespace App\Repository;

use App\Entity\UserCompetence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCompetence|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCompetence|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCompetence[]    findAll()
 * @method UserCompetence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCompetenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCompetence::class);
    }

    // /**
    //  * @return UserCompetence[] Returns an array of UserCompetence objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserCompetence
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
