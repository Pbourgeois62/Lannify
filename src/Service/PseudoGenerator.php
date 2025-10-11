<?php

namespace App\Service;

class PseudoGenerator
{
    private array $adjectives = [
        'Fou', 'Chouette', 'Rigolo', 'Marrant', 'Super', 'Giga', 'Petit', 'Drôle', 'Savant'
    ];

    private array $nouns = [
        'Panda', 'Croissant', 'Escargot', 'Fromage', 'Chaton', 'Licorne', 'Lutin', 'Tortue', 'Gâteau'
    ];

    public function generate(): string
    {
        $adj = $this->adjectives[array_rand($this->adjectives)];
        $noun = $this->nouns[array_rand($this->nouns)];
        return "$adj $noun";
    }
}
