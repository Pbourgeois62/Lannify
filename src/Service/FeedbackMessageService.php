<?php
namespace App\Service;

use App\Entity\Feedback;
use App\Entity\FeedbackMessage;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class FeedbackMessageService
{
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    public function sendAdminMessage(Feedback $feedback, string $content): void
    {
        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $admin = $user;
                break;
            }
        }        

        $message = new FeedbackMessage();
        $message->setContent($content ?? "Bonjour, ceci est un message automatique de l'administrateur.")
            ->setFeedback($feedback);
        $message->setSender($admin);
        $feedback->addMessage($message);

        $this->em->persist($message);
        $this->em->flush();
    }
}
