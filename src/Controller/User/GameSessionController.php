<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Game;
use App\Entity\Image;
use App\Form\EventType;
use App\Entity\GameSession;
use App\Service\ChatService;
use App\Form\GameSessionType;
use App\Form\EventUserChoiceType;
use App\Repository\EventRepository;
use App\Service\MagicLinkGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/game-session')]
final class GameSessionController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    #[Route('/{id}/home', name: 'game_session_home')]
    public function home(GameSession $gameSession): Response
    {
        $chatData = $this->chatService->getChatData($gameSession);       

        return $this->render('game_session/home.html.twig', array_merge(
            $chatData, 
            [
            'gameSession' => $gameSession,
        ]));
    }

    #[Route('/create', name: 'game_session_create')]
    public function create(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, MagicLinkGenerator $magicLinkGenerator): Response
    {
        $gameSession = new GameSession();

        // $gameSession->setCoverImage(new Image());

        $form = $this->createForm(GameSessionType::class, $gameSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $coverImage = $gameSession->getCoverImage();
            // if ($coverImage) {
            //     $coverImage->setEvent($gameSession);
            // }

            $gameSession->setOrganizer($user);
            // $gameSession->setMagicToken($magicLinkGenerator->generate($gameSession));
            $gameSession->addParticipant($user);

            $em->persist($gameSession);
            $em->flush();

            $this->addFlash('success', 'Événement multi créé avec succès !');

            return $this->redirectToRoute('game_session_home', ['id' => $gameSession->getId()]);
        }

        return $this->render('game_session/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/join/{token}', name: 'game_session_join')]
    public function join(
        #[CurrentUser] ?User $user,
        string $token,
        EntityManagerInterface $em,
        EventRepository $eventRepository
    ): Response {
        $event = $eventRepository->findOneBy(['magicToken' => $token]);
        if (!$event) {
            $this->addFlash('error', 'Lien invalide.');
            return $this->redirectToRoute('user_home');
        }

        if ($user && !$event->getUsers()->contains($user)) {
            $event->addUser($user);
            $em->flush();
            $this->addFlash('success', 'Vous avez rejoint l’événement !');
        }

        return $this->redirectToRoute('game_session_home', ['id' => $event->getId()]);
    }


    #[Route('/{gameSession}/edit', name: 'game_session_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $event->getCoverImage();
            if ($coverImage) {
                $coverImage->setEvent($event);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement mis à jour avec succès !');

            return $this->redirectToRoute('game_session_home', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }


    #[Route('/{gameSession}/delete', name: 'game_session_delete')]
    public function delete(Event $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Événement supprimé avec succès !');

        return $this->redirectToRoute('user_home');
    }

    #[Route('/{gameSession}/close', name: 'game_session_close')]
    public function close(Event $event, EntityManagerInterface $em): Response
    {
        $event->setClosed(true);
        $em->flush();
        $this->addFlash('success', 'Événement cloturé avec succès !');

        return $this->redirectToRoute('user_home');
    }


    #[Route('/{gameSession}/manage', name: 'game_session_manage')]
    public function manage(Event $event, Request $request, EntityManagerInterface $em): Response
    {
        $chatData = $this->chatService->getChatData($event);

        $form = $this->createForm(EventUserChoiceType::class, null, [
            'users' => $event->getUsers()->toArray(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getOrganizer() === $this->getUser()) {
                $selectedUser = $form->get('userchoice')->getData();
                $event->setOrganizer($selectedUser);
                $em->flush();
                $this->addFlash('success', 'Organisateur changé avec succès !');
            } else {
                $this->addFlash('error', 'Vous n’êtes pas autorisé à changer l’organisateur.');
            }

            return $this->redirectToRoute('user_home_manage', ['id' => $event->getId()]);
        }

        return $this->render('game_session/manage.html.twig', array_merge($chatData, [
            'event' => $event,
            'form' => $form
        ]));
    }
}
