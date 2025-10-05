<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Event;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    //    /**
    //     * @return Event[] Returns an array of Event objects
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

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function getAllPassedEvents(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->andWhere('e.endDate < :now')
            ->setParameter('now', $now)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getAllUpcomingEvents(): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->andWhere('e.endDate >= :now')
            ->setParameter('now', $now)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getPassedEventsForUser(User $user): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->andWhere('u = :user')
            ->andWhere('e.endDate < :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUpcomingEventsForUser(User $user): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->andWhere('u = :user')
            ->andWhere('e.endDate >= :now')
            ->setParameter('user', $user)
            ->setParameter('now', $now)
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getOpenedEventsForUser(User $user): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->andWhere('u = :user')            
            ->andWhere('e.isClosed = false')
            ->setParameter('user', $user)           
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getClosedEventsForUser(User $user): array
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('e')
            ->innerJoin('e.users', 'u')
            ->andWhere('u = :user')            
            ->andWhere('e.isClosed = true')
            ->setParameter('user', $user)           
            ->orderBy('e.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
