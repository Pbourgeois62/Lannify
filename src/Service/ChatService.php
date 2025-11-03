<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Message;
use App\Entity\GameSession;
use App\Repository\MessageRepository;
use Symfony\Component\Mercure\Update;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;

class ChatService
{
    private MessageRepository $messageRepository;
    private string $mercurePublicUrl;
    private HubInterface $hub;
    private EntityManagerInterface $em;

    public function __construct(MessageRepository $messageRepository, string $mercurePublicUrl, HubInterface $hub, EntityManagerInterface $em)
    {
        $this->messageRepository = $messageRepository;
        $this->em = $em;
        $this->hub = $hub;
        $this->mercurePublicUrl = rtrim($mercurePublicUrl, '/');
    }

    /**
     * Retourne les données nécessaires pour afficher un chat
     *
     * @param Event|GameSession $context
     */
    public function getChatData(object $context, ?User $user = null): array
    {
        if ($context instanceof Event) {
            $criteria = ['event' => $context];
            $topicType = 'event';
        } elseif ($context instanceof GameSession) {
            $criteria = ['gameSession' => $context];
            $topicType = 'game-session';
        } else {
            throw new \InvalidArgumentException('Type d’entité non pris en charge pour le chat.');
        }

        $messages = $this->messageRepository->findBy($criteria, ['createdAt' => 'ASC']);

        $mercureUrl = sprintf(
            '%s?topic=%s',
            $this->mercurePublicUrl,
            urlencode("/{$topicType}/{$context->getId()}/chat")
        );

        return [
            'messages' => $messages,
            'mercureUrl' => $mercureUrl,
        ];
    }

    /**
     * Publie un message dans le chat d’un Event ou d’une GameSession.
     *
     * @param Event|GameSession $context
     */
    public function postMessage(object $context, User $user, string $content): void
    {
        if ($context instanceof Event) {
            $topic = "/event/{$context->getId()}/chat";
            $message = (new Message())
                ->setContent($content)
                ->setSender($user)
                ->setEvent($context);
        } elseif ($context instanceof GameSession) {
            $topic = "/game-session/{$context->getId()}/chat";
            $message = (new Message())
                ->setContent($content)
                ->setSender($user)
                ->setGameSession($context);
        } else {
            throw new \InvalidArgumentException('Type d’entité non pris en charge pour le chat.');
        }

        $this->em->persist($message);
        $this->em->flush();

        // Publie la mise à jour Mercure
        $this->hub->publish(new Update(
            $topic,
            json_encode([
                'id' => $message->getId(),
                'userId' => $user->getId(),
                'user' => $user->getProfile()?->getNickname() ?? $user->getEmail(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('H:i'),
                'avatar' => $user->getProfile()?->getAvatarPath(),
                'new_message' => '/audio/new_message.mp3',
            ], JSON_THROW_ON_ERROR)
        ));
    }
}
