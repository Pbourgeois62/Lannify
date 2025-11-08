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

    #[LiveProp]
    public ?string $errorMessage = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(GameChoiceType::class);
    }

    #[LiveAction]
    public function search(RawgClient $rawgService): void
    {
        $query = trim($this->query ?? '');
        if ($query === '') {
            $this->rawgData = [];
            $this->errorMessage = null;
            return;
        }

        try {
            $data = $rawgService->searchGames($query, 1, 500);
            $this->rawgData = $data['results'] ?? [];
            $this->errorMessage = null;
        } catch (\Throwable $e) {
            $this->rawgData = [];
            $this->errorMessage = 'Erreur RAWG : ' . $e->getMessage();
        }
    }

    #[LiveAction]
    public function selectGame(
        #[LiveArg] int $id,
        RawgClient $rawgService,
        EntityManagerInterface $em
    ): mixed {
        if (!$this->initialFormData instanceof GameSession) {
            $this->errorMessage = 'Session de jeu non trouvée.';
            return null;
        }

        try {
            $game = $em->getRepository(Game::class)->findOneBy(['rawgId' => $id]);

            if (!$game) {
                $gameData = $rawgService->getGame($id);
                if (!$gameData || isset($gameData['error'])) {
                    $this->errorMessage = 'Impossible de récupérer les infos du jeu.';
                    return null;
                }

                $game = new Game();
                $game->setRawgId($gameData['id']);
                $game->setSource('rawg');                
                $em->persist($game);
            }

            $gameSession = $this->initialFormData;
            $gameSession->setGame($game);

            $em->persist($gameSession);
            $em->flush();

            return $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erreur interne : ' . $e->getMessage();
            return null;
        }
    }

    #[LiveAction]
    public function clear(): void
    {
        $this->query = null;
        $this->rawgData = [];
        $this->errorMessage = null;
    }
}
