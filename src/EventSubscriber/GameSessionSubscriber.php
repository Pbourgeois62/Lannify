<?php
namespace App\EventSubscriber;

use Doctrine\ORM\Events;
use App\Entity\GameSession;
use App\Service\RawgClient;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class GameSessionSubscriber implements EventSubscriber
{
    private RawgClient $rawgClient;

    public function __construct(RawgClient $rawgClient)
    {
        $this->rawgClient = $rawgClient;
    }

    public function getSubscribedEvents(): array
    {
        return [Events::prePersist, Events::preUpdate];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->updateCoverImage($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->updateCoverImage($args);
    }

    private function updateCoverImage(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof GameSession) {
            return;
        }

        if ($entity->getRawgId()) {
            $rawgData = $this->rawgClient->getGame($entity->getRawgId());
            if ($rawgData && isset($rawgData['background_image'])) {
                $entity->setCoverImageUrl($rawgData['background_image']);
            }
        }
    }
}
