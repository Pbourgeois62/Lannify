<?php

namespace App\Controller\User;

use App\Entity\Game;
use App\Entity\User;
use App\Entity\Event;
use App\Form\GameType;
use App\Service\ChatService;
use App\Entity\ParticipantGame;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantGameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\{IsGranted, CurrentUser};

#[IsGranted('ROLE_USER')]
#[Route('/event/{event}/games')]
final class EventGameController extends AbstractController
{    
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    #[Route('/', name: 'event_games', methods: ['GET'])]
    public function show(
        #[CurrentUser] User $user,
        Event $event,
        ParticipantGameRepository $pgRepo,
        EntityManagerInterface $em
    ): Response {
        $chatData = $this->chatService->getChatData($event);

        foreach ($event->getGames() as $game) {
            $pg = $pgRepo->findOneBy([
                'participant' => $user,
                'game' => $game,
            ]);

            if (!$pg) {
                $pg = (new ParticipantGame())
                    ->setParticipant($user)
                    ->setGame($game);

                $em->persist($pg);
            }
        }

        $em->flush();

        return $this->render('event/games/index.html.twig', array_merge($chatData, [
            'event' => $event,
        ]));
    }

    #[Route('add', name: 'game_add')]
    public function add(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $game = new Game();
        $game->setEvent($event);

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($game);
            $em->flush();
            $this->addFlash('success', 'Jeu ajouté');

            return $this->redirectToRoute('event_games', ['event' => $event->getId()]);
        }

        return $this->render('event/games/form.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
