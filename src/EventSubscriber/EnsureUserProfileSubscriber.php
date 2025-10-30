<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\ProfileManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class EnsureUserProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProfileManager $profileManager
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof User) {
            $this->profileManager->ensureUserProfile($user);
        }
    }
}
