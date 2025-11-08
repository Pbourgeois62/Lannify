<?php

namespace App\Controller\Admin;

use App\Entity\GameSession;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GameSessionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/game-sessions')]
final class GameSessionManagementController extends AbstractController
{
    #[Route('/', name: 'admin_game_sessions_management')]
    public function list(GameSessionRepository $gameSessionRepository): Response
    {
        $gameSessions = $gameSessionRepository->findall(SORT_DESC);
        return $this->render('admin/game_sessions/list.html.twig', [
            'gameSessions' => $gameSessions,
        ]);
    }

    #[Route('/delete/{gameSession}', name: 'admin_game_session_delete', methods: ['POST', 'GET'])]
    public function delete(GameSession $gameSession, EntityManagerInterface $em): Response
    {
        $em->remove($gameSession, true);
        $em->flush();

        $this->addFlash('success', 'Evénement multi supprimé avec succès.');

        return $this->redirectToRoute('admin_game_sessions_management');
    }

    #[Route('/edit-magicToken/{gameSession}', name: 'admin_game_session_edit_magicToken', methods: ['POST', 'GET'])]
    public function editMagicLink(GameSession $gameSession, EntityManagerInterface $em, TokenGenerator $TokenGenerator): Response
    {        
        $gameSession->setMagicToken($TokenGenerator->generateMagicLinkToken('lan'));      
        
        $em->flush();

        $this->addFlash('success', 'Le lien magique a été régénéré avec succès.');

        return $this->redirectToRoute('admin_game_sessions_management');
    }
}
