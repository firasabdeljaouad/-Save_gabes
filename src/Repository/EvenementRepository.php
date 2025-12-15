<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }


    /**
     * Find events by status
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', $status)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all events (returns Query for pagination)
     */
    public function findAllQuery(): Query
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.typeEvenement', 't')
            ->addSelect('t')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery();
    }

    /**
     * Find upcoming events (returns Query for pagination)
     */
    public function findUpcomingEventsQuery(): Query
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.typeEvenement', 't')
            ->addSelect('t')
            ->andWhere('e.dateDebut >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery();
    }

    /**
     * Find events by type
     */
    public function findByType(int $typeId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.typeEvenement = :typeId')
            ->setParameter('typeId', $typeId)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search events by title, description, or location
     */
    public function searchEvents(string $searchTerm): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.titre LIKE :searchTerm OR e.description LIKE :searchTerm OR e.lieu LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by date range
     */
    public function findByDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dateDebut BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events with high participation
     */
    public function findPopularEvents(int $minParticipants = 50): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.nombreParticipants >= :minParticipants')
            ->setParameter('minParticipants', $minParticipants)
            ->orderBy('e.nombreParticipants', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find events by multiple criteria (returns Query for pagination)
     */
    public function findEventsByCriteriaQuery(array $criteria): Query
    {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.typeEvenement', 't')
            ->addSelect('t')
            ->orderBy('e.dateDebut', 'ASC');

        if (isset($criteria['type']) && $criteria['type']) {
            $qb->andWhere('e.typeEvenement = :type')
                ->setParameter('type', $criteria['type']);
        }

        if (isset($criteria['status']) && $criteria['status']) {
            $qb->andWhere('e.status = :status')
                ->setParameter('status', $criteria['status']);
        }

        if (isset($criteria['search']) && $criteria['search']) {
            $qb->andWhere('e.titre LIKE :search OR e.description LIKE :search OR e.lieu LIKE :search OR e.adresse LIKE :search')
                ->setParameter('search', '%' . $criteria['search'] . '%');
        }

        if (isset($criteria['date_from']) && $criteria['date_from']) {
            $dateFrom = \DateTime::createFromFormat('Y-m-d', $criteria['date_from']);
            if ($dateFrom) {
                $qb->andWhere('e.dateDebut >= :dateFrom')
                    ->setParameter('dateFrom', $dateFrom);
            }
        }

        if (isset($criteria['date_to']) && $criteria['date_to']) {
            $dateTo = \DateTime::createFromFormat('Y-m-d', $criteria['date_to']);
            if ($dateTo) {
                $dateTo->setTime(23, 59, 59);
                $qb->andWhere('e.dateFin <= :dateTo')
                    ->setParameter('dateTo', $dateTo);
            }
        }

        return $qb->getQuery();
    }

    /**
     * Get events count by status
     */
    public function getEventsCountByStatus(): array
    {
        return $this->createQueryBuilder('e')
            ->select('e.status, COUNT(e.id) as count')
            ->groupBy('e.status')
            ->getQuery()
            ->getResult();
    }


    public function findEventsByCriteria(array $criteria): array
    {
        return $this->findEventsByCriteriaQuery($criteria)->getResult();
    }

    public function findUpcomingEvents(): array
    {
        return $this->findUpcomingEventsQuery()->getResult();
    }

    //    /**
    //     * @return Evenement[] Returns an array of Evenement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Evenement
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
