<?php

namespace App\Repository;

use App\Entity\NeedContribution;
use App\Entity\User;
use App\Entity\Need;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NeedContribution>
 */
class NeedContributionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NeedContribution::class);
    }

    /**
     * Contributions d’un user pour un besoin donné
     *
     * @return NeedContribution[]
     */
    public function findByNeedAndUser(Need $need, User $user): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.need = :need')
            ->andWhere('c.user = :user')
            ->setParameter('need', $need)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Total d’items apportés par un user sur un Event (tous besoins confondus)
     */
    public function countUserContributionsInEvent(User $user, int $eventId): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('SUM(c.quantity)')
            ->join('c.need', 'n')
            ->andWhere('c.user = :user')
            ->andWhere('n.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $eventId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
