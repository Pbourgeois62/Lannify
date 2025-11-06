<?php

namespace App\Twig\Components;

use App\Entity\Game;
use App\Entity\GameSession;
use App\Service\RawgClient;
use App\Form\GameChoiceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[AsLiveComponent]
class GameSearchComponent extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    public array $rawgData = [];

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp]
    public ?GameSession $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(GameChoiceType::class);
    }

    #[LiveAction]
    public function search(RawgClient $rawgService): void
    {
        $searchQuery = $this->query;
        $this->rawgData = $rawgService->searchGames($searchQuery, 1, 500);
    }

    #[LiveAction]
    public function selectGame(
        #[LiveArg] int $id,
        RawgClient $rawgService,
        EntityManagerInterface $em
    ): mixed {
        if (!$this->initialFormData instanceof GameSession) {
            return null;
        }

        $game = $em->getRepository(Game::class)->findOneBy(['rawgId' => $id]);

        if (!$game) {
            $gameData = $rawgService->getGame($id);
            $game = new Game();
            $game->setRawgId($gameData['id']);
            // $game->setLabel($gameData['name'] ?? '');
            $game->setSource('rawg');
            $em->persist($game);
        }

        $gameSession = $this->initialFormData;
        $gameSession->setGame($game);

        $em->persist($gameSession);
        $em->flush();

        return $this->redirectToRoute('user_home');
    }


    #[LiveAction]
    public function clear(): void
    {
        $this->query = null;
        $this->rawgData = [];
    }
}
