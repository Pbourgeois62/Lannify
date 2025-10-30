<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class ToastManager
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getToasts(): array
    {
        $session = $this->requestStack->getSession();
        $toasts = [];

        if (!$session->get('feedback_seen', false)) {
            $toasts['feedback'] = [
                'id' => 'feedback',
                'title' => 'Aidez-nous à améliorer Lannify !',
                'lines' => [
                    'Une idée, une remarque ou un bug à signaler ?',
                    'Vos retours sont essentiels pour faire évoluer Lannify dans la bonne direction !',
                    'Vous pouvez gérer vos retours à tout moment depuis votre profil.',
                ],
                'buttons' => [
                    [
                        'label' => 'Faire un retour',
                        'route' => 'feedback_index',
                        'class' => 'btn-primary',
                    ],
                    [
                        'label' => 'Je ne veux plus voir ce message',
                        'route' => 'feedback_seen',
                        'class' => 'btn-secondary',
                    ],
                ],
            ];
        }
        return $toasts;
    }
}
