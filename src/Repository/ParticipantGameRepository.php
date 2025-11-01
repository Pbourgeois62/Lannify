<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\ParticipantGame;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ParticipantGame>
 */
class ParticipantGameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantGame::class);
    }

    //    /**
    //     * @return ParticipantGame[] Returns an array of ParticipantGame objects
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

    //    public function findOneBySomeField($value): ?ParticipantGame
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    // src/Repository/ParticipantGameRepository.php
    public function findByEvent(Event $event): array
    {
        return $this->createQueryBuilder('pg')
            ->join('pg.game', 'g')
            ->where('g.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getResult();
    }

    public function findReadyUsers(int $gameId): array
{
    return $this->createQueryBuilder('pg')
        ->join('pg.participant', 'p')
        ->addSelect('p') // sélectionne l'entité User complète
        ->where('pg.game = :gameId')
        ->andWhere('pg.owns = true') // ou autre condition pour "ready"
        ->setParameter('gameId', $gameId)
        ->getQuery()
        ->getResult(); // retourne un tableau d'objets User
}

}
