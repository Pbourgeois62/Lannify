<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Event;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/event/{id}/game/add', name: 'game_add')]
    public function add(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $game = new Game();
        $game->setEvent($event);

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($game);
            $em->flush();
            $this->addFlash('success', 'Jeu ajouté ✅');

            return $this->redirectToRoute('event_games', ['id' => $event->getId(), '_fragment' => 'games']);
        }

        return $this->render('game/add.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }
}
