<?php

namespace App\Service;

class PseudoGenerator
{
    private array $adjectiveGroups = [
        'petits' => ['Petit', 'Mignon', 'Joli', 'Rigolo'],
        'amusants' => ['Drôle', 'Chouette', 'Savant', 'Super'],
        'puissants' => ['Giga', 'Énorme', 'Féroce', 'Majestueux']
    ];

    private array $nouns = [
        'Petit' => ['Chaton', 'Lutin', 'Gâteau', 'Croissant'],
        'Drôle' => ['Licorne', 'Escargot', 'Tortue', 'Panda'],
        'Giga' => ['Dragon', 'Tyrannosaure', 'Robot', 'Château']
    ];

    private array $separators = [' ', '-', '_'];
    private array $suffixes = ['', 'X', '42', '007', 'Z', 'Pro', 'Max'];

    public function generate(): string
    {
        // On choisit d'abord un groupe d'adjectifs
        $group = array_rand($this->adjectiveGroups);
        $adj = $this->randomizeCase($this->adjectiveGroups[$group][random_int(0, count($this->adjectiveGroups[$group]) - 1)]);

        // On choisit un nom compatible avec l'adjectif
        $possibleNouns = $this->nouns[$adj] ?? $this->flattenNouns();
        $noun = $this->randomizeCase($possibleNouns[random_int(0, count($possibleNouns) - 1)]);

        $sep = $this->separators[random_int(0, count($this->separators) - 1)];
        $suffix = $this->suffixes[random_int(0, count($this->suffixes) - 1)];

        // Ajouter un nombre aléatoire si aucun suffixe choisi
        if ($suffix === '') {
            $suffix = random_int(1, 99);
        }

        return $adj . $sep . $noun . $suffix;
    }

    private function flattenNouns(): array
    {
        $all = [];
        foreach ($this->nouns as $list) {
            $all = array_merge($all, $list);
        }
        return $all;
    }

    private function randomizeCase(string $word): string
    {
        return ucfirst(strtolower($word)); // On privilégie toujours la première lettre majuscule
    }
}
