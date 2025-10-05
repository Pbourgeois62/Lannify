<?php
namespace App\Service;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;

class MercureJwtGenerator
{
    private Configuration $jwtConfig;

    public function __construct(string $secret)
    {
        $this->jwtConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );
    }

    public function generate(?int $eventId = null, ?int $userId = null): string
    {
        $now = new DateTimeImmutable();

        $builder = $this->jwtConfig->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('mercure', [
                // Vous pouvez aussi utiliser l'opérateur spread si vous voulez un tableau simple
                'subscribe' => $eventId ? ["/event/{$eventId}/chat"] : ["/event/*/chat"]
            ]);

        if ($userId) {
            // Le claim 'sub' (subject) est une convention standard et peut être préférable à 'userId'
            $builder = $builder->withClaim('sub', (string) $userId); 
        }

        // CORRECTION : La méthode getToken() sur le builder permet de générer et signer le token
        return $builder->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())->toString();
    }
}