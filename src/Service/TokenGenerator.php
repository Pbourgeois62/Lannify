<?php

namespace App\Service;

class TokenGenerator
{
    /**
     * Caractères lisibles pour les codes partageables
     */
    private array $readableChars = [
        'A','B','C','D','E','F','G','H','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z',
        '2','3','4','5','6','7','8','9'
    ];

    /**
     * Génère un token pour un lien magique ou une invitation.
     * 
     * @param string $seed Une chaîne utilisée pour générer le token (ex: ID + date)
     * @param int $length Longueur du token retourné
     * @return string
     */
    public function generateMagicLinkToken(string $seed, int $length = 32): string
    {
        return substr(hash('sha256', $seed . random_int(1000, 9999)), 0, $length);
    }

    /**
     * Génère un code user-friendly partageable.
     * 
     * @param string $prefix Préfixe pour identifier le type d’événement ou usage (ex: EVT)
     * @param string $seed Chaîne utilisée pour générer le code (ex: date + ID)
     * @param int $length Longueur du code final (hors préfixe)
     * @return string
     */
    public function generateShareableCode(string $prefix, string $seed, int $length = 8): string
    {
        $hash = strtoupper(substr(md5($seed), 0, $length));
        $code = '';

        for ($i = 0; $i < strlen($hash); $i++) {
            $index = hexdec($hash[$i]) % count($this->readableChars);
            $code .= $this->readableChars[$index];
        }

        return $prefix . '-' . $code;
    }
}
