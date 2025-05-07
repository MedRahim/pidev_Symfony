<?php

namespace App\Service;

class ContentFilter
{
    private array $badWords;

    public function __construct()
    {
        // Liste des mots à filtrer
        $this->badWords = [
            'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'dick', 'cock', 'pussy', 'faggot',
            'slut', 'whore', 'cunt', 'motherfucker', 'nigger', 'negro', 'kike', 'spic', 'chink',
            'tranny', 'dyke', 'retard', 'cripple', 'twat', 'jackass', 'wanker', 'bollocks',
            'nigga', 'hoe', 'skank', 'pig', 'pedo', 'rapist', 'molester', 'coon', 'gook',
            'camel jockey', 'raghead', 'sand nigger', 'gyppo', 'beaner', 'homo', 'faggot',
            'merde', 'putain', 'connard', 'salope', 'enculé', 'nique', 'pute', 'bordel', 'con',
            'enfoiré', 'fdp', 'tg', 'ntm', 'salaud', 'bite', 'couille', 'chatte', 'enculeur',
             'bougnoule', 'nègre', 'youpin', 'bicot', 'raton', 'batard', 'branleur', 'tapette',
            'pédé', 'pédale', 'gouine', 'travelo', 'enculée', 'pute à clic', 'face de rat'
          ];
    }

    public function filter(string $text): string
    {
        // Convertit le texte en minuscules pour la comparaison
        $lowerText = mb_strtolower($text);
        
        // Remplace chaque mot interdit par ****
        foreach ($this->badWords as $word) {
            $pattern = '/(?<=\b|\s)' . preg_quote($word, '/') . '(?=\b|\s)/i';
            $text = preg_replace($pattern, '****', $text);
        }
        
        return $text;
    }

    public function containsBadWords(string $text): bool
    {
        $originalText = $text;
        $filteredText = $this->filter($text);
        return $originalText !== $filteredText;
    }
} 