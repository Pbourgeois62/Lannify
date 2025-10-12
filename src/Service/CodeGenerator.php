<?php

namespace App\Service;

use App\Entity\Event;

class CodeGenerator
{
    public function generateEventCode(Event $event): string
    {
        return 'EVT-' . strtoupper(substr(md5($event->getId() . $event->getCreatedAt()->format('YmdHis')), 0, 8));
    }
}
