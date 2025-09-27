<?php
namespace App\Twig;

use App\Entity\Event;
use App\Repository\MessageRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MessagesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            // Utilisable comme: {{ event_messages(event) }}
            new TwigFunction('event_messages', [$this, 'getMessages']),
        ];
    }

    public function __construct(private MessageRepository $messageRepo) {}

    /**
     * Retourne la liste des messages d’un event
     */
    public function getMessages(Event $event): array
    {
        return $this->messageRepo->findBy(
            ['event' => $event],
            ['createdAt' => 'ASC']
        );
    }
}
