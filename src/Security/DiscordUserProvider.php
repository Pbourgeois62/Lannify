<?php

namespace App\Security;

use App\Entity\User;
use App\Service\ProfileManager;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class DiscordUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProfileManager $profileManager
    ) {}

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $data = $response->getData();

        $discordId = $data['id'] ?? null;
        $email = $response->getEmail();
        $nickname = $response->getNickname();
        $avatarHash = $data['avatar'] ?? null;

        $avatarUrl = $discordId && $avatarHash
            ? sprintf('https://cdn.discordapp.com/avatars/%s/%s.png', $discordId, $avatarHash)
            : null;

        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['discordId' => $discordId]);

        if (!$user && $email) {
            $user = $repo->findOneBy(['email' => $email]);
        }

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setDiscordId($discordId);
            $this->em->persist($user);
        } else {
            $user->setDiscordId($discordId);
        }

        // Appel du service pour s’assurer que le profil est créé
        $this->profileManager->ensureUserProfile($user);

        // Si tu veux mettre à jour l'avatar Discord
        if ($user->getProfile()) {
            $user->getProfile()->setAvatarUrl($avatarUrl);
        }

        $this->em->flush();
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with identifier "%s" not found.', $identifier));
        }

        return $user;
    }
}
