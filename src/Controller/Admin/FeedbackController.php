<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Feedback;
use App\Form\FeedbackType;
use App\Entity\FeedbackMessage;
use App\Form\FeedbackMessageType;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/feedback')]
final class FeedbackController extends AbstractController
{
    #[Route('/', name: 'admin_feedback_index')]
    public function index(FeedbackRepository $feedbackRepository): Response
    {
        $feedbacks = $feedbackRepository->findAllOrderByUserAndDate();
        return $this->render('admin/feedback/index.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }

    #[Route('/new', name: 'feedback_new')]
    public function new(#[CurrentUser] User $user, Request $request, EntityManagerInterface $em): Response
    {
        $feedback = new Feedback();
        $feedback->setAuthor($user);

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($feedback);
            $em->flush();

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
}
