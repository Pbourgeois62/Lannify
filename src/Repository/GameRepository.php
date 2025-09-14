<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    //    /**
    //     * @return Game[] Returns an array of Game objects
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

    //    public function findOneBySomeField($value): ?Game
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function countParticipantsOwningGame(int $gameId): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('COUNT(pg.id)')
            ->from('App\Entity\ParticipantGame', 'pg')
            ->where('pg.game = :gameId')
            ->andWhere('pg.owns = :owns')
            ->setParameter('gameId', $gameId)
            ->setParameter('owns', true);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countParticipantsInterestedInGame(int $gameId): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select('COUNT(pg.id)')
            ->from('App\Entity\ParticipantGame', 'pg')
            ->where('pg.game = :gameId')
            ->andWhere('pg.interested = :interested')
            ->setParameter('gameId', $gameId)
            ->setParameter('interested', true);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countReadyGames(int $gameId): int
    {
        return $this->createQueryBuilder('g')
            ->select('COUNT(pg.id)')
            ->join('g.participantGames', 'pg')
            ->where('g.id = :gameId')
            ->andWhere('pg.owns = true')
            ->andWhere('pg.interested = true')
            ->setParameter('gameId', $gameId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
