<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\GameSession;
use App\Service\RawgClient;
use App\Service\ChatService;
use App\Form\GameSessionType;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GameSessionRepository;
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
    public function home(GameSession $gameSession, RawgClient $rawgService): Response
    {
        $chatData = $this->chatService->getChatData($gameSession);
        if ($gameSession->getGame()) {
            $gameData = $rawgService->getGame($gameSession->getGame()->getRawgId());
        }
        return $this->render('game_session/home.html.twig', array_merge(
            $chatData,
            [
                'gameSession' => $gameSession,
                'gameData' => $gameData
            ]
        ));
    }

    #[Route('/{id}/show', name: 'game_session_show')]
    public function show(RawgClient $rawgService, GameSession $gameSession): Response
    {
        $gameData = null;
        $chatData = $this->chatService->getChatData($gameSession);
        if ($gameSession->getGame()) {
            $gameData = $rawgService->getGame($gameSession->getGame()->getRawgId());
        }
        return $this->render('game_session/show.html.twig', array_merge(
            $chatData,
            [
                'gameSession' => $gameSession,
                'gameData' => $gameData,
            ]
        ));
    }

    #[Route('/create', name: 'game_session_create')]
    public function create(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, TokenGenerator $tokenGenerator): Response
    {
        $gameSession = new GameSession();
        $gameSession->setCurrentStep(1);

        $form = $this->createForm(GameSessionType::class, $gameSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $gameSession->setOrganizer($user);
            $gameSession->setMagicToken($tokenGenerator->generateMagicLinkToken('game_session'));
            if ($form->get('isPrivate')->getData()) {
                $gameSession->setIsPrivate(true);
                $gameSession->setPrivateCode($tokenGenerator->generateShareableCode('GMS', 'game_session_' . uniqid()));
            } else {
                $gameSession->setIsPrivate(false);
            }
            $gameSession->addParticipant($user);    
            $gameSession->setCurrentStep(2);        
            $em->persist($gameSession);
            $em->flush();

            $this->addFlash('success', 'Session multi créé avec succès !');

            return $this->redirectToRoute('game_session_choose_game', [
                'id' => $gameSession->getId()
            ]);
        }

        return $this->render('game_session/form.html.twig', [
            'form' => $form,
            'isEdit' => false,
            'gameSession' => $gameSession
        ]);
    }
    #[Route('/{id}/choose-game', name: 'game_session_choose_game')]
    public function chooseGame(
        GameSession $gameSession
    ): Response {
        return $this->render('game_session/choose_game.html.twig', [
            'gameSession' => $gameSession
        ]);
    }

    #[Route('/{id}/confirmation', name: 'game_session_confirmation')]
    public function confirmSessionCreation(
        GameSession $gameSession
    ): Response {
        $gameSession->setCurrentStep(4);
        return $this->render('game_session/confirmation.html.twig', [
            'gameSession' => $gameSession
        ]);
    }


    #[Route('/join/{token}', name: 'game_session_join')]
    public function join(
        #[CurrentUser] ?User $user,
        string $token,
        EntityManagerInterface $em,
        GameSessionRepository $gameSessionRepository
    ): Response {
        $gameSession = $gameSessionRepository->findOneBy(['magicToken' => $token]);
        if (!$gameSession) {
            $this->addFlash('error', 'Lien invalide.');
            return $this->redirectToRoute('user_home');
        }

        if ($gameSession && $gameSession->countParticipants() >= $gameSession->getMaxParticipants()) {
            $this->addFlash('error', 'Le lobby est complet !');
            return $this->redirectToRoute('user_home');
        }

        if ($user && !$gameSession->getParticipants()->contains($user)) {
            $gameSession->addUser($user);
            $em->flush();
            $this->addFlash('success', 'Vous avez rejoint l’événement !');
        }

        return $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]);
    }

    #[Route('/game-session/{gameSession}/resume/{step}', name: 'game_session_resume_step')]
    public function resumeStep(GameSession $gameSession, int $step): Response
    {
        $gameSession->setCurrentStep($step);

        return match ($step) {
            1=> $this->redirectToRoute('game_session_edit_context', ['gameSession' => $gameSession->getId()]),
            2 => $this->redirectToRoute('game_session_edit_game', ['gameSession' => $gameSession->getId()]),
            default => $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]),
        };
    }



    #[Route('/game-session/{gameSession}/edit/context', name: 'game_session_edit_context')]
    public function editContext(GameSession $gameSession, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GameSessionType::class, $gameSession);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($gameSession);
            $em->flush();

            $this->addFlash('success', 'Session de jeu mise à jour avec succès !');

            return $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]);
        }

        return $this->render('game_session/form.html.twig', [
            'form' => $form,
            'isEdit' => true,
            'gameSession' => $gameSession
        ]);
    }

    #[Route('/game-session/{gameSession}/edit/game', name: 'game_session_edit_game')]
    public function editGame(GameSession $gameSession): Response
    {
        return $this->render('game_session/choose_game.html.twig', [
            'gameSession' => $gameSession
        ]);
    }



    #[Route('/{gameSession}/delete', name: 'game_session_delete')]
    public function delete(GameSession $gameSession, EntityManagerInterface $em): Response
    {
        $em->remove($gameSession);
        $em->flush();
        $this->addFlash('success', 'session multi supprimé avec succès !');

        return $this->redirectToRoute('user_home');
    }
    // #[Route('/access', name: 'game_session_access_by_code')]
    // public function accessByCode(Request $request, GameSessionRepository $gameSessionRepository): Response
    // {
    //     $privateCode = $request->query->get('quick_access_code')['privateCode'] ?? null;

    //     if (!$privateCode) {
    //         $this->addFlash('error', 'Veuillez entrer un code.');
    //         return $this->redirectToRoute('user_home');
    //     }

    //     $gameSession = $gameSessionRepository->findOneBy(['privateCode' => $privateCode]);

    //     if (!$gameSession) {
    //         $this->addFlash('error', 'Code privé invalide.');
    //         return $this->redirectToRoute('user_home');
    //     }

    //     return $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]);
    // }


    #[Route('/{gameSession}/manage', name: 'game_session_manage')]
    public function manage(GameSession $gameSession, Request $request, EntityManagerInterface $em): Response
    {
        $chatData = $this->chatService->getChatData($gameSession);

        // $form = $this->createForm(EventUserChoiceType::class, null, [
        //     'users' => $event->getUsers()->toArray(),
        // ]);

        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     if ($event->getOrganizer() === $this->getUser()) {
        //         $selectedUser = $form->get('userchoice')->getData();
        //         $event->setOrganizer($selectedUser);
        //         $em->flush();
        //         $this->addFlash('success', 'Organisateur changé avec succès !');
        //     } else {
        //         $this->addFlash('error', 'Vous n’êtes pas autorisé à changer l’organisateur.');
        //     }

        //     return $this->redirectToRoute('user_home_manage', ['id' => $event->getId()]);
        // }

        return $this->render('game_session/manage.html.twig', array_merge($chatData, [
            'gameSession' => $gameSession,
            // 'form' => $form
        ]));
    }
}
