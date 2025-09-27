<?php
// src/Twig/MercureExtension.php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MercureExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('mercure_subscribe_url', [$this, 'getSubscribeUrl']),
        ];
    }

    public function getSubscribeUrl(): string
    {
        return $_ENV['MERCURE_PUBLIC_URL'] ?? 'http://localhost/.well-known/mercure';
    }
}