<?php

namespace App\Twig\Components;

use App\Entity\Event;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantGameRepository;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent]
class GamePreferencesComponent extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public Event $event;

    public array $participantGames = [];
    public array $readyCounts = [];
    public array $readyUsers = [];

    public function __construct(
        private EntityManagerInterface $em,
        private ParticipantGameRepository $pgRepo,
        private GameRepository $gameRepository
    ) {}

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->refreshData();
    }

    #[LiveAction]
    public function toggleOwned(#[LiveArg] int $id): void
    {
        $pg = $this->pgRepo->findOneBy([
            'game' => $id,
            'participant' => $this->getUser(),
        ]);

        if ($pg) {
            $pg->setOwns(!$pg->getOwns());
            $this->em->flush();
        }

        $this->refreshData();
    }

    #[LiveAction]
    public function toggleInterested(#[LiveArg] int $id): void
    {
        $pg = $this->pgRepo->findOneBy([
            'game' => $id,
            'participant' => $this->getUser(),
        ]);

        if ($pg) {
            $pg->setInterested(!$pg->getInterested());
            $this->em->flush();
        }

        $this->refreshData();
    }

    private function refreshData(): void
    {
        $this->participantGames = [];
        $this->readyCounts = [];
        $this->readyUsers = [];

        foreach ($this->event->getGames() as $game) {
            $pg = $this->pgRepo->findOneBy([
                'participant' => $this->getUser(),
                'game' => $game,
            ]);

            $this->participantGames[$game->getId()] = $pg;
            $this->readyCounts[$game->getId()] = $this->gameRepository->countReadyGames($game->getId());
            $this->readyUsers[$game->getId()] = $this->pgRepo->findReadyUsers($game->getId());
        }
    }
}
