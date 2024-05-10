<?php

namespace App\Repository;

use App\Entity\MenuDay;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuDay>
 *
 * @method MenuDay|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuDay|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuDay[]    findAll()
 * @method MenuDay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuDayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuDay::class);
    }

//    /**
//     * @return MenuDay[] Returns an array of MenuDay objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MenuDay
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
