<?php

namespace App\Controller\User;

use App\Entity\Event;
use App\Entity\Message;
use App\Service\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[IsGranted('ROLE_USER')]
class EventChatController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }   

    #[Route('/event/{id}/chat', name: 'event_chat_post', methods: ['POST'])]
    public function postMessage(
        #[CurrentUser] \App\Entity\User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        HubInterface $hub
    ): JsonResponse {
        $content = trim($request->request->get('content', ''));
        if ($content === '') {
            return new JsonResponse(['error' => 'Empty message'], Response::HTTP_BAD_REQUEST);
        }

        $message = (new Message())
            ->setContent($content)
            ->setSender($user)
            ->setEvent($event);

        $em->persist($message);
        $em->flush();

       $avatarUrl = $user->getProfile()->getAvatar();

        $hub->publish(new Update(
            "/event/{$event->getId()}/chat",
            json_encode([
                'id' => $message->getId(),
                'userId' => $user->getId(),
                'user' => $user->getProfile()?->getNickname() ?? $user->getEmail(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('H:i'),
                'avatar' => $avatarUrl,
                'defaultAvatarUrl' => '/images/default-avatar.webp',
                'new_message' =>'/audio/new_message.mp3'
            ], JSON_THROW_ON_ERROR)
        ));

        return new JsonResponse(['success' => true]);
    }
}
