<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Entity\FeedbackMessage;
use App\Form\FeedbackMessageType;
use App\Repository\FeedbackRepository;
use App\Service\FeedbackMessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
#[Route('/feedback')]
final class FeedbackController extends AbstractController
{
    #[Route('/', name: 'feedback_index')]
    public function index(#[CurrentUser] User $user, FeedbackRepository $feedbackRepository): Response
    {
        $feedbacks = $feedbackRepository->findBy(['author' => $user], ['createdAt' => 'DESC']);
        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/new', name: 'feedback_new')]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em, FeedbackMessageService $feedbackMessageService): Response
    {
        $feedback = new Feedback();
        $feedback->setAuthor($user);

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();
            $content = "Bonjour et merci pour votre retour !\n\nNous allons étudier votre demande et vous recontacter si besoin.\n\nÀ très vite sur Lannify !";
            $feedbackMessageService->sendAdminMessage($feedback, $content);

            $this->addFlash('success', 'Votre retour a bien été envoyé ! Merci !');

            return $this->redirectToRoute('feedback_show', ['feedback' => $feedback->getId()]);
        }

        return $this->render('feedback/form.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{feedback}/show', name: 'feedback_show')]
    public function show(#[CurrentUser] User $user, Feedback $feedback, Request $request, EntityManagerInterface $em): Response
    {
        $feedbackMessage = new FeedbackMessage();
        $form = $this->createForm(FeedbackMessageType::class, $feedbackMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $feedback->addMessage($feedbackMessage);
            $feedbackMessage->setSender($user);
            $em->persist($feedbackMessage);
            $em->flush();
            $this->addFlash('success', 'Votre message a bien été envoyé ! Merci !');
            return $this->redirectToRoute('feedback_show', ['feedback' => $feedback->getId()]);
        }
        return $this->render('feedback/show.html.twig', [
            'feedback' => $feedback,
            'form' => $form,
        ]);
    }

    #[Route('/seen', name: 'feedback_seen')]
public function markFeedbackSeen(Request $request): Response
{
    $request->getSession()->set('feedback_seen', true);
    // Redirige vers la homepage pour ne pas rester sur cette route
    return $this->redirectToRoute('home');
}
}
