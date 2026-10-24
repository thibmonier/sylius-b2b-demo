<?php

declare(strict_types=1);

namespace App\Demo\Home;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** US-186: `demo_home_photo('hero')` for the photo partial, `demo_home_photos()` for the credits page. */
final class HomeTwigExtension extends AbstractExtension
{
    public function __construct(private readonly HomePhotos $photos)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('demo_home_photo', $this->photos->get(...)),
            new TwigFunction('demo_home_photos', $this->photos->all(...)),
        ];
    }
}
