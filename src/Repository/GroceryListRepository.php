<?php

namespace App\Repository;

use App\Entity\GroceryList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GroceryList>
 *
 * @method GroceryList|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroceryList|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroceryList[]    findAll()
 * @method GroceryList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroceryListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroceryList::class);
    }

//    /**
//     * @return GroceryList[] Returns an array of GroceryList objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GroceryList
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
