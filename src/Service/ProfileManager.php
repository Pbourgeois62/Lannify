<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Profile;
use App\Service\PseudoGenerator;
use Doctrine\ORM\EntityManagerInterface;

class ProfileManager
{
    public function __construct(
        private PseudoGenerator $pseudoGenerator,
        private EntityManagerInterface $em
    ) {}

    public function ensureUserProfile(User $user): void
    {
        if ($user->getProfile() === null) {
            $profile = new Profile();
            $profile->setNickname($this->pseudoGenerator->generate());

            $user->setProfile($profile);

            $this->em->persist($profile);
            $this->em->flush();
        }
    }
}
