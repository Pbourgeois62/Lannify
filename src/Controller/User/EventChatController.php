<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Message;
use App\Entity\GameSession;
use App\Service\ChatService;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
class EventChatController extends AbstractController
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    // #[Route('/event/{id}/chat', name: 'event_chat_post', methods: ['POST'])]
    // public function postMessage(
    //     #[CurrentUser] User $user,
    //     Event $event,
    //     Request $request,
    // ): JsonResponse {
    //     $content = trim($request->request->get('content', ''));
    //     if ($content === '') {
    //         return new JsonResponse(['error' => 'Empty message'], Response::HTTP_BAD_REQUEST);
    //     }

    //     $this->chatService->postMessage($event, $user, $content);

    //     return new JsonResponse(['success' => true]);
    // }
    #[Route('/{type}/{id}/chat', name: 'chat_post', methods: ['POST'])]
public function postMessage(
    #[CurrentUser] User $user,
    string $type,
    int $id,
    Request $request,
    EntityManagerInterface $em,
): JsonResponse {
    $content = trim($request->request->get('content', ''));
    if ($content === '') {
        return new JsonResponse(['error' => 'Empty message'], Response::HTTP_BAD_REQUEST);
    }

    // On récupère l’entité selon le type
    $entity = match ($type) {
        'event' => $em->getRepository(Event::class)->find($id),
        'game-session' => $em->getRepository(GameSession::class)->find($id),
        default => null,
    };

    if (!$entity) {
        return new JsonResponse(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
    }

    // Et on délègue à ton ChatService
    $this->chatService->postMessage($entity, $user, $content);

    return new JsonResponse(['success' => true]);
}

}
