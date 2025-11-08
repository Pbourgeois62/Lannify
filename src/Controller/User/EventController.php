<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Image;
use App\Form\EventType;
use App\Form\EventUserChoiceType;
use App\Service\ChatService;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/event')]
final class EventController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    #[Route('/{id}/home', name: 'event_home')]
    public function home(Event $event): Response
    {
        $chatData = $this->chatService->getChatData($event);        

        return $this->render('event/home.html.twig', array_merge($chatData, [
            'event' => $event,            
        ]));
    }

    #[Route('/create', name: 'event_create')]
    public function create(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, TokenGenerator $TokenGenerator): Response
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

            $event->setOrganizer($user);
            $event->setMagicToken($TokenGenerator->generateMagicLinkToken('lan'));
            $event->addUser($user);

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');

            return $this->redirectToRoute('event_home', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => false,
        ]);
    }

    #[Route('/join/{token}', name: 'event_join')]
    public function join(
        #[CurrentUser] ?User $user,
        string $token,
        EntityManagerInterface $em,
        \App\Repository\EventRepository $eventRepository
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

        return $this->redirectToRoute('event_home', ['id' => $event->getId()]);
    }


    #[Route('/{event}/edit', name: 'event_edit')]
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

            return $this->redirectToRoute('event_home', ['id' => $event->getId()]);
        }

        return $this->render('event/form.html.twig', [
            'form' => $form->createView(),
            'isEdit' => true,
        ]);
    }


    #[Route('/{event}/delete', name: 'event_delete')]
    public function delete(Event $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();
        $this->addFlash('success', 'Événement supprimé avec succès !');

        return $this->redirectToRoute('user_home');
    }

    #[Route('/{event}/close', name: 'event_close')]
    public function close(Event $event, EntityManagerInterface $em): Response
    {
        $event->setClosed(true);
        $em->flush();
        $this->addFlash('success', 'Événement cloturé avec succès !');

        return $this->redirectToRoute('user_home');
    }


    #[Route('/{event}/manage', name: 'event_manage')]
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

            return $this->redirectToRoute('event_manage', ['id' => $event->getId()]);
        }

        return $this->render('event/manage.html.twig', array_merge($chatData, [
            'event' => $event,
            'form' => $form
        ]));
    }
}
