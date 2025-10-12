<?php

namespace App\Service;

use App\Entity\Event;

class MagicLinkGenerator
{
    public function generate(Event $event): string
    {
        // Génère un token unique basé sur l'ID + date + random
        return hash('sha256', $event->getId() . $event->getName() . random_int(1000, 9999));
    }
}
