<?php

namespace App\Controller\User;

use Dom\Entity;
use App\Entity\User;
use App\Service\ProfileManager;
use App\Form\QuickAccessCodeType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\GameSessionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/user')]
final class HomeController extends AbstractController
{
    #[Route('/home', name: 'user_home')]
    public function index(
        #[CurrentUser] User $user,
        EventRepository $eventRepository,
        Request $request,
        GameSessionRepository $gameSessionRepository,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(QuickAccessCodeType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $privateCode = $data['privateCode'];
            $gameSession = $gameSessionRepository->findOneBy(['privateCode' => $privateCode]);
            $gameSession->addParticipant($user);
            $em->flush();
            if (!$gameSession) {
                $this->addFlash('error', 'Code privé invalide.');
                return $this->redirectToRoute('user_home');
            }
            return $this->redirectToRoute('game_session_show', ['id' => $gameSession->getId()]);
        }
        $session = $request->getSession();

        $showFeedback = !$session->get('feedback_seen', false);

        $openedEvents = $eventRepository->getOpenedEventsForUser($user);
        $closedEvents = $eventRepository->getClosedEventsForUser($user);

        return $this->render('home/index.html.twig', [
            'openedEvents' => $openedEvents,
            'closedEvents' => $closedEvents,
            'showFeedback' => $showFeedback,
            'gameSessions' => $user->getGameSessions(),
            'quickAccessForm' => $form->createView(), // passer à Twig
        ]);
    }
}
