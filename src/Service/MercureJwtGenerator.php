<?php

namespace App\Service;

use Lcobucci\JWT\Configuration;

class MercureJwtGenerator
{
    private string $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function createPublisherToken(): string
    {
        $config = Configuration::forSymmetricSigner(
            new \Lcobucci\JWT\Signer\Hmac\Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::plainText($this->secret)
        );

        $now = new \DateTimeImmutable();
        $token = $config->builder()
            ->issuedAt($now)
            ->expiresAt($now->modify('+1 hour'))
            ->withClaim('mercure', ['publish' => ['*']])
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }
}
