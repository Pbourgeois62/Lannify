<?php
namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\MessageRepository;

class ChatService
{
    private MessageRepository $messageRepository;
    private string $mercurePublicUrl;

    public function __construct(MessageRepository $messageRepository, string $mercurePublicUrl)
    {
        $this->messageRepository = $messageRepository;
        $this->mercurePublicUrl = rtrim($mercurePublicUrl, '/');
    }

    /**
     * Retourne les données nécessaires pour afficher un chat
     */
    public function getChatData(Event $event, ?User $user = null): array
    {
        $messages = $this->messageRepository->findBy(
            ['event' => $event],
            ['createdAt' => 'ASC']
        );

        $mercureUrl = $this->mercurePublicUrl . '?topic=' . urlencode("/event/{$event->getId()}/chat");

        return [
            'messages' => $messages,
            'mercureUrl' => $mercureUrl,
        ];
    }
}
