<?php

declare(strict_types=1);

namespace App\Demo\Controller;

use App\Demo\OverrideGuide\OverrideGuideExamples;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/** US-172: how to adapt a plugin without modifying it, with real examples of this demo. */
final readonly class OverrideGuideController
{
    public function __construct(
        private Environment $twig,
        private OverrideGuideExamples $examples,
    ) {
    }

    #[Route('/{_locale}/surcharge', name: 'app_demo_override_guide', requirements: ['_locale' => '[a-z]{2}_[A-Z]{2}'], methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response($this->twig->render('demo/override_guide.html.twig', [
            'examples' => $this->examples->all(),
            'last_resort' => $this->examples->lastResort(),
            'excerpts' => $this->excerpts(),
        ]));
    }

    /** @return array<string, string> the lines to show, by case */
    private function excerpts(): array
    {
        $excerpts = [];
        foreach ([...$this->examples->all(), $this->examples->lastResort()] as $example) {
            $excerpts[$example->case] = $this->examples->excerpt($example);
        }

        return $excerpts;
    }
}
