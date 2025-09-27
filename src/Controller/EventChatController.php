<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventChatController extends AbstractController
{
    /**
     * ➡️ Affiche le composant chat avec les anciens messages
     */
    #[Route('/event/{id}/chat-box', name: 'event_chat_box', methods: ['GET'])]
    public function chatBox(Event $event, MessageRepository $messageRepo): Response
    {
        // Récupère les messages existants de l'événement
        $messages = $messageRepo->findBy(
            ['event' => $event],
            ['createdAt' => 'ASC'] // du plus ancien au plus récent
        );

        return $this->render('event/home.html.twig', [
            'event' => $event,
            'messages' => $messages,
        ]);
    }

    /**
     * ➡️ Reçoit un nouveau message, l’enregistre et le publie via Mercure
     */
    #[Route('/event/{id}/chat', name: 'event_chat_post', methods: ['POST'])]
    public function postMessage(
        #[CurrentUser] ?User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        HubInterface $hub
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $content = trim((string) $request->request->get('content'));
        if ($content === '') {
            return new JsonResponse(['error' => 'Empty message'], 400);
        }

        // Sauvegarde du message en BDD
        $message = new Message();
        $message
            ->setContent($content)
            ->setSender($user)
            ->setEvent($event);

        $em->persist($message);
        $em->flush();

        // Publication temps réel via Mercure
        $update = new Update(
            "/event/{$event->getId()}/chat",
            json_encode([
                'id'       => $message->getId(),
                'userId'   => $user->getId(),
                'user'     => $user->getProfile()?->getNickname() ?? $user->getEmail(),
                'content'  => $message->getContent(),
                'createdAt'=> $message->getCreatedAt()->format('H:i'),
            ], JSON_THROW_ON_ERROR)
        );

        $hub->publish($update);

        return new JsonResponse(['success' => true]);
    }
}
