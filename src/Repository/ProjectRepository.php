<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
<<<<<<< Updated upstream
=======
/*
public function findListDonationByIdProject(int $id):array
{
    return $this->createQueryBuilder('p')
        ->where('p.id = :id')
        ->setParameter('id', $id)
        ->orderBy('p.donations', 'DESC')
        ->getQuery()
        ->getResult()
        ;
}*/
    public function findAllQuery(): Query
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.startDate', 'ASC')
            ->getQuery();
    }

    public function findProjectByCriteriaQuery(array $criteria): Query
    {
        $q = $this->createQueryBuilder('p')
            ->orderBy('p.startDate', 'ASC');

        if (isset($criteria['status']) && $criteria['status']) {
            $q->andWhere('p.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        if (isset($criteria['search']) && $criteria['search']) {
            $q->andWhere('p.name LIKE :search OR p.description  LIKE :search ')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (isset($criteria['date_from']) && $criteria['date_from']) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $criteria['date_from']);
            if ($dateFrom) {
                $q->andWhere('p.startDate >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            }
        }

        if (isset($criteria['date_to']) && $criteria['date_to']) {
            $dateTo = \DateTime::createFromFormat('Y-m-d', $criteria['date_to']);
            if ($dateTo) {
                $dateTo->setTime(23, 59, 59);
                $q->andWhere('p.dateFin <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }
        }
        return $q->getQuery();
    }

>>>>>>> Stashed changes

//    /**
//     * @return Project[] Returns an array of Project objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Project
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
