<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('unique', [$this, 'uniqueFilter']),
        ];
    }

    public function uniqueFilter($array): array
    {
        if (!is_array($array)) {
            return [];
        }
        return array_unique($array);
    }
} 