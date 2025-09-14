<?php

namespace App\Repository;

use App\Entity\Need;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Need>
 */
class NeedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Need::class);
    }

    /**
     * Récupère tous les besoins d’un Event, triés par libellé
     *
     * @return Need[]
     */
    public function findByEvent(Event $event): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.event = :event')
            ->setParameter('event', $event)
            ->orderBy('n.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne la liste des besoins encore non couverts
     *
     * @return Need[]
     */
    public function findUncoveredNeeds(Event $event): array
    {
        return $this->createQueryBuilder('n')
            ->leftJoin('n.contributions', 'c')
            ->addSelect('c')
            ->andWhere('n.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
        // ⚠️ La logique "non couvert" (isFullyCovered = false) se traite côté PHP avec la méthode de l’entité
    }
}
