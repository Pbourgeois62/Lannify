<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Commentary;
use App\Entity\EventImage;
use App\Form\CommentaryType;
use App\Form\EventImageType;
use App\Service\ChatService;
use App\Repository\EventImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/event/{event}')]
final class EventPhotoController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    #[Route('/photos/index', name: 'event_photo_index', methods: ['GET', 'POST'])]
    public function index(
        #[CurrentUser] ?User $user,
        Event $event,
        EventImageRepository $eventImageRepository,
        Request $request,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory
    ): Response {
        $chatData = $this->chatService->getChatData($event);

        $organizer = $event->getOrganizer();
        $photos = ($user && $organizer && $user->getId() === $organizer->getId())
            ? $event->getEventImages()
            : $eventImageRepository->getAllApprovedImagesByEventId($event->getId());

        $commentForms = [];
        $submittedForm = null;

        foreach ($photos as $photo) {
            $comment = new Commentary();
            $comment->setEventImage($photo);
            if ($user) {
                $comment->setAuthor($user);
            }

            $form = $formFactory->createNamed(
                'comment_' . $photo->getId(),
                CommentaryType::class,
                $comment,
                [
                    'action' => $this->generateUrl('event_photo_index', ['event' => $event->getId()]),
                    'attr' => ['id' => 'comment_form_' . $photo->getId()],
                ]
            );

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $submittedForm = $form;
            }

            $commentForms[$photo->getId()] = $form->createView();
        }

        if ($submittedForm) {
            $em->persist($submittedForm->getData());
            $em->flush();
            $this->addFlash('success', 'Commentaire ajouté avec succès.');

            return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
        }

        return $this->render('event/photos/index.html.twig', array_merge($chatData, [
            'photos' => $photos,
            'event' => $event,
            'commentForms' => $commentForms,
        ]));
    }

    #[Route('/photo/add', name: 'event_photo_add', methods: ['GET', 'POST'])]
    public function add(
        #[CurrentUser] User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $eventImage = new EventImage();
        $form = $this->createForm(EventImageType::class, $eventImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventImage->setEvent($event);
            $eventImage->setUploadedBy($user);

            $em->persist($eventImage);
            $em->flush();

            $this->addFlash('success', 'Photo ajoutée avec succès. Elle sera visible une fois validée par l’organisateur.');
            return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
        }

        return $this->render('event/photos/form.html.twig', [
            'form' => $form,
            'event' => $event,
        ]);
    }

    #[Route('/photo/{photo}/delete', name: 'event_photo_delete', methods: ['POST', 'GET'])]
    public function delete(
        EventImage $photo,
        EntityManagerInterface $em
    ): Response {
        $event = $photo->getEvent();

        $em->remove($photo);
        $em->flush();

        $this->addFlash('success', 'Photo supprimée avec succès.');
        return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
    }

    #[Route('/photo/{photo}/approve', name: 'event_photo_approve', methods: ['POST', 'GET'])]
    public function approve(
        EventImage $photo,
        #[CurrentUser] User $user,
        EntityManagerInterface $em
    ): Response {
        $event = $photo->getEvent();

        if (!$user || $user !== $event->getOrganizer()) {
            throw $this->createAccessDeniedException('Action non autorisée.');
        }

        $photo->setIsApproved(true);
        $em->flush();

        $this->addFlash('success', 'Photo validée.');
        return $this->redirectToRoute('event_photo_index', ['event' => $event->getId()]);
    }
}
