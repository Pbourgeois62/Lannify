<?php
namespace App\Controller\User;

use App\Entity\Event;
use App\Entity\Message;
use App\Service\MercureJwtGenerator;
use App\Repository\MessageRepository;
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
    #[Route('/event/{id}/chat-box', name: 'event_chat_box', methods: ['GET'])]
    public function chatBox(
        #[CurrentUser] ?\App\Entity\User $user,
        Event $event,
        MessageRepository $messageRepo,
        MercureJwtGenerator $jwtGenerator
    ): Response {
        $messages = $messageRepo->findBy(['event' => $event], ['createdAt' => 'ASC']);
        $jwt = $jwtGenerator->generate($event->getId(), $user->getId());

        $mercureUrl = ($_ENV['MERCURE_PUBLIC_URL'] ?? 'http://localhost/.well-known/mercure')
            . '?topic=' . urlencode("/event/{$event->getId()}/chat")
            . '&jwt=' . $jwt;

        return $this->render('event/home.html.twig', [
            'event' => $event,
            'messages' => $messages,
            'mercureUrl' => $mercureUrl,
        ]);
    }

    #[Route('/event/{id}/chat', name: 'event_chat_post', methods: ['POST'])]
    public function postMessage(
        #[CurrentUser] ?\App\Entity\User $user,
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        HubInterface $hub
    ): JsonResponse {
        if (!$user) return new JsonResponse(['error' => 'Unauthorized'], 401);

        $content = trim((string)$request->request->get('content'));
        if ($content === '') return new JsonResponse(['error' => 'Empty message'], 400);

        $message = new Message();
        $message->setContent($content)->setSender($user)->setEvent($event);

        $em->persist($message);
        $em->flush();

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
