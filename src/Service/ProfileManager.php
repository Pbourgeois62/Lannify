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

    /**
     * S'assure que l'utilisateur possède un profil minimal.
     * Ne remplace jamais un pseudo ou un avatar déjà défini.
     */
    public function ensureUserProfile(User $user): void
    {
        $profile = $user->getProfile();

        if ($profile === null) {
            $profile = new Profile();
            $user->setProfile($profile);
        }

        if (!$profile->getNickname()) {
            $profile->setNickname($this->pseudoGenerator->generate());
        }

        $this->em->persist($profile);
        $this->em->persist($user);
        $this->em->flush();
    }
}
