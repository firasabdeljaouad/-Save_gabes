<?php

namespace App\Repository;

use App\Entity\Benevole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Benevole>
 */
class BenevoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Benevole::class);
    }

    /**
     * @return \Doctrine\ORM\Query Returns a Query for Benevole objects filtered by activite and name
     */
    public function findByActiviteQuery(int $activiteId, ?string $name = null): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('b')
            ->join('b.activites', 'a')
            ->andWhere('a.id = :activiteId')
            ->setParameter('activiteId', $activiteId);

        if ($name) {
            $qb->andWhere('b.nom LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        return $qb->getQuery();
    }

    //    /**
    //     * @return Benevole[] Returns an array of Benevole objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Benevole
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
