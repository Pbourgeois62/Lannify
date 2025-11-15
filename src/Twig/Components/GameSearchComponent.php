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

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public array $cachedPages = [];

    #[LiveProp(writable: true)]
    public array $rawgData = [];

    #[LiveProp]
    public ?GameSession $gameSession = null;

    #[LiveProp(writable: true)]
    public ?string $errorMessage = null;

    #[LiveProp(writable: true)]
    public int $maxPageLoaded = 1;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(GameChoiceType::class);
    }

    #[LiveAction]
    public function search(RawgClient $rawgService): void
    {
        $this->page = 1;
        $this->cachedPages = [];
        $this->maxPageLoaded = 1;
        $this->loadPage($rawgService, 1);
    }

    #[LiveAction]
    public function goToPage(RawgClient $rawgService, #[LiveArg] int $page): void
    {
        $this->page = $page;

        // ⚡ Si déjà en cache, ne recharge pas depuis RAWG
        if (isset($this->cachedPages[$page])) {
            $this->rawgData = $this->cachedPages[$page];
            return;
        }

        $this->loadPage($rawgService, $page);
    }

    #[LiveAction]
    public function nextPage(RawgClient $rawgService): void
    {
        $this->goToPage($rawgService, $this->page + 1);
    }

    #[LiveAction]
    public function clear(): void
    {
        $this->query = null;
        $this->page = 1;
        $this->cachedPages = [];
        $this->rawgData = [];
        $this->maxPageLoaded = 1;
        $this->errorMessage = null;
    }

    private function loadPage(RawgClient $rawgService, int $page): void
    {
        $query = trim($this->query ?? '');
        if ($query === '') {
            $this->rawgData = [];
            $this->errorMessage = null;
            return;
        }

        try {
            $pageSize = 8;
            $data = $rawgService->searchGames($query, $page, $pageSize);
            $results = $data['results'] ?? [];

            $this->cachedPages[$page] = $results;
            $this->rawgData = $results;

            if ($page > $this->maxPageLoaded) {
                $this->maxPageLoaded = $page;
            }

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
        if (!$this->gameSession instanceof GameSession) {
            $this->errorMessage = 'Session de jeu non trouvée.';
            return null;
        }

        try {
            $gameData = $rawgService->getGame($id);
            if (!$gameData || isset($gameData['error'])) {
                $this->errorMessage = 'Impossible de récupérer les infos du jeu.';
                return null;
            }

            $game = $em->getRepository(Game::class)->findOneBy(['rawgId' => $id]);
            if (!$game) {
                $game = new Game();
                $game->setRawgId($gameData['id']);
                $game->setSource('rawg');
                $em->persist($game);
            }

            $gameSession = $this->gameSession;
            $gameSession->setGame($game);
            $gameSession->setCoverImageUrl($gameData['background_image'] ?? null);
            $gameSession->setCurrentStep(3);

            $em->persist($gameSession);
            $em->flush();

            return $this->redirectToRoute('game_session_confirmation', [
                'id' => $gameSession->getId(),
            ]);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erreur interne : ' . $e->getMessage();
            return null;
        }
    }
}
