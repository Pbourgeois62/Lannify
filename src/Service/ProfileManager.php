<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Profile;
use App\Entity\Image;
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

            // 🟢 Création de l'avatar par défaut
            $defaultAvatar = new Image();
            $defaultAvatar->setImageName('default-avatar.webp');
            $profile->setAvatar($defaultAvatar);

            $user->setProfile($profile);

            $this->em->persist($defaultAvatar);
            $this->em->persist($profile);
            $this->em->flush();
        }
    }
}
