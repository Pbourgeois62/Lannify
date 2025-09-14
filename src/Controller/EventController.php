<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Image;
use App\Form\EventType;
use App\Service\CodeGenerator;
use App\Entity\ParticipantGame;
use App\Entity\NeedContribution;
use App\Form\NeedContributionType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ParticipantGameRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NeedContributionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('s/', name: 'event_index')]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', [
            'controller_name' => 'EventController',
        ]);
    }

    #[Route('/create', name: 'event_create')]
    public function create(Request $request, EntityManagerInterface $em, CodeGenerator $code): Response
    {
        $event = new Event();

        $event->setCoverImage(new Image());

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverImage = $event->getCoverImage();
            if ($coverImage) {
                $coverImage->setEvent($event);
            }
            $event->setCode($code->generateEventCode($event));
            $event->setOrganizer($this->getUser());
            $event->addUser($this->getUser());

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event_infos', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/{id}/countdown', name: 'event_countdown')]
    public function countdown(Event $event): Response
    {
        return $this->render('event/countdown.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/infos', name: 'event_infos')]
    public function showInfos(Event $event): Response
    {
        return $this->render('event/infos.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/participants', name: 'event_participants')]
    public function showParticipants(Event $event): Response
    {
        return $this->render('event/participants.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/needs', name: 'event_needs')]
    public function showNeeds(Event $event, #[CurrentUser] ?User $user, NeedContributionRepository $needContributionRepository): Response
    {
        $forms = [];

        foreach ($event->getNeeds() as $need) {
            $contribution = $needContributionRepository->findOneBy([
                'need' => $need,
                'user' => $user,
            ]) ?? (new NeedContribution())->setNeed($need)->setUser($user);

            $form = $this->createForm(NeedContributionType::class, $contribution, [
                'action' => $this->generateUrl('need_contribute', ['id' => $need->getId()]),
                'method' => 'POST',
                'remaining_quantity' => $need->getRemainingQuantityNotFromUser($user),
            ]);

            $forms[$need->getId()] = $form->createView();
        }

        return $this->render('event/needs.html.twig', [
            'event' => $event,
            'forms' => $forms,
        ]);
    }

    #[Route('/{id}/games', name: 'event_games', methods: ['GET'])]
    public function showGames(Event $event, ParticipantGameRepository $pgRepo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        foreach ($event->getGames() as $game) {
            $pg = $pgRepo->findOneBy([
                'participant' => $user,
                'game' => $game,
            ]);

            if (!$pg) {
                $pg = new ParticipantGame();
                $pg->setParticipant($user)
                    ->setGame($game);

                $em->persist($pg);
            }
        }

        $em->flush();

        return $this->render('event/games.html.twig', [
            'event' => $event,
        ]);
    }



    #[Route('/join', name: 'event_join')]
    public function join(EventRepository $eventRepository, EntityManagerInterface $em, Request $request): Response
    {
        $code = $request->query->get('code');
        $event = $eventRepository->findOneBy(['code' => $code]);
        if (!$event) {
            $this->addFlash('error', 'Code d’événement invalide.');
            return $this->redirectToRoute('home');
        }
        $user = $this->getUser();
        if ($user && !$event->getUsers()->contains($user)) {
            $event->addUser($user);
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', 'Vous avez rejoint la Lan avec succès !');
        } else {
            $this->addFlash('info', 'Vous participez déjà à cette Lan.');
        }
        return $this->redirectToRoute('event_infos', [
            'id' => $event->getId(),
        ]);
    }

    #[Route('/{id}/edit', name: 'event_edit')]
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

            return $this->redirectToRoute('event_infos', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }


    #[Route('/{id}/delete', name: 'event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $em): Response
    {
        if ($event->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException("Vous n'êtes pas l'organisateur de cet événement.");
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $em->remove($event);
            $em->flush();
            $this->addFlash('success', 'Événement supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('home');
    }
}
