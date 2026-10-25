<?php

declare(strict_types=1);

namespace App\Demo\Images;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** US-197: `demo_photo_position(product.code)` gives the `object-position` of the photo of a product, or null (default framing). */
final class PhotoFramingTwigExtension extends AbstractExtension
{
    public function __construct(private readonly PhotoFraming $framing)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('demo_photo_position', $this->framing->objectPosition(...))];
    }
}
