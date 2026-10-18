<?php

declare(strict_types=1);

namespace App\Demo\Images;

/**
 * US-092: the rules every row of the image manifest must meet. An image is shown only when its licence is free (CC0) and
 * a person reviewed it against its product (`validated`), or when it is a neutral category visual drawn for the demo
 * (`fallback`): a photo that does not fit its product is never kept (cf. docs/images-produits.md).
 */
final class ImageManifestAuditor
{
    /**
     * @param iterable<array<string, string>> $rows
     *
     * @return list<string> one "SKU: problem" line per violation
     */
    public function violations(iterable $rows): array
    {
        $violations = [];
        foreach ($rows as $row) {
            foreach ($this->check($row) as $problem) {
                $violations[] = ($row['sku'] ?? '?') . ': ' . $problem;
            }
        }

        return $violations;
    }

    /**
     * @param array<string, string> $row
     *
     * @return list<string>
     */
    private function check(array $row): array
    {
        $problems = [];
        foreach (ImageManifest::FIELDS as $field) {
            if (($row[$field] ?? '') === '') {
                $problems[] = $field . ' is empty';
            }
        }
        if (($row['license'] ?? '') !== 'cc0' || !\str_contains($row['license_url'] ?? '', 'publicdomain/zero')) {
            $problems[] = 'licence is not CC0';
        }
        if (!str_starts_with($row['license_url'] ?? '', 'https://')) {
            $problems[] = 'license_url must be an https URL';
        }
        $status = $row['status'] ?? '';
        if (!\in_array($status, [ImageManifest::STATUS_VALIDATED, ImageManifest::STATUS_FALLBACK], true)) {
            $problems[] = \sprintf('status "%s" is not reviewed (validated or fallback expected)', $status);
        }
        $isFallback = $status === ImageManifest::STATUS_FALLBACK;
        if ($isFallback !== (($row['scope'] ?? '') === 'category') || $isFallback !== (($row['source'] ?? '') === ImageManifest::SOURCE_GENERATED)) {
            $problems[] = 'a fallback must be a generated category visual, and a category visual must be a fallback';
        } elseif (!$isFallback && !\in_array($row['scope'] ?? '', ['own', 'family'], true)) {
            $problems[] = 'scope must be own or family';
        }

        return $problems;
    }
}
