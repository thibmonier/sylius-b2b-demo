#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generates SVG placeholder images for every product SKU found in the catalogue CSV files.
 *
 * Each image is written to public/media/fixtures/<SKU>.svg.
 * The script is idempotent: existing files are skipped unless --force is passed.
 *
 * Usage:
 *   php bin/generate-fixtures-images.php
 *   php bin/generate-fixtures-images.php --force
 */

$projectDir = \dirname(__DIR__);
$csvDir = $projectDir . '/fixtures/csv';
$outputDir = $projectDir . '/public/media/fixtures';
$force = \in_array('--force', $argv ?? [], true);

$csvFiles = ['epi.csv', 'quincaillerie.csv', 'fournitures.csv'];

// Palette by category prefix
$palette = [
    'EPI'  => ['bg' => '#1f3a5f', 'fg' => '#ffffff'],
    'VISS' => ['bg' => '#5f3a1f', 'fg' => '#ffffff'],
    'OUTI' => ['bg' => '#3a5f1f', 'fg' => '#ffffff'],
    'FIXA' => ['bg' => '#5f1f3a', 'fg' => '#ffffff'],
    'EMBA' => ['bg' => '#1f5f5f', 'fg' => '#ffffff'],
    'SIGN' => ['bg' => '#5f5f1f', 'fg' => '#000000'],
    'PAPE' => ['bg' => '#3a1f5f', 'fg' => '#ffffff'],
];

$generated = 0;
$skipped = 0;
$errors = 0;

foreach ($csvFiles as $csvFile) {
    $csvPath = $csvDir . '/' . $csvFile;
    if (!\file_exists($csvPath)) {
        \fwrite(\STDERR, "[WARN] CSV not found: $csvPath\n");
        continue;
    }

    $handle = \fopen($csvPath, 'r');
    if ($handle === false) {
        \fwrite(\STDERR, "[ERROR] Cannot open: $csvPath\n");
        ++$errors;
        continue;
    }

    // Read header
    $header = \fgetcsv($handle);
    if (!\is_array($header)) {
        \fclose($handle);
        continue;
    }

    $skuIndex = \array_search('sku', $header, true);
    $nameFrIndex = \array_search('name_fr', $header, true);
    $taxonIndex = \array_search('taxon', $header, true);

    if ($skuIndex === false) {
        \fclose($handle);
        continue;
    }

    while (($row = \fgetcsv($handle)) !== false) {
        if ($row === [null]) {
            continue;
        }

        $sku = \trim($row[$skuIndex] ?? '');
        if ($sku === '') {
            continue;
        }

        $nameFr = ($nameFrIndex !== false) ? \trim($row[$nameFrIndex] ?? $sku) : $sku;
        $taxon = ($taxonIndex !== false) ? \trim($row[$taxonIndex] ?? '') : '';

        $outputPath = $outputDir . '/' . $sku . '.svg';
        if (!$force && \file_exists($outputPath)) {
            ++$skipped;
            continue;
        }

        // Pick colour by SKU prefix (first segment before -)
        $prefix = \strtoupper(\explode('-', $sku)[1] ?? '');
        $colors = $palette[$prefix] ?? ['bg' => '#444444', 'fg' => '#ffffff'];

        $svg = generateSvg($sku, $nameFr, $taxon, $colors['bg'], $colors['fg']);
        if (\file_put_contents($outputPath, $svg) === false) {
            \fwrite(\STDERR, "[ERROR] Cannot write: $outputPath\n");
            ++$errors;
        } else {
            ++$generated;
        }
    }

    \fclose($handle);
}

\printf(
    "Done: %d generated, %d skipped (already exist), %d errors.\n",
    $generated,
    $skipped,
    $errors,
);
exit($errors > 0 ? 1 : 0);

// ─────────────────────────────────────────────────────────────────────────────

function generateSvg(string $sku, string $label, string $taxon, string $bg, string $fg): string
{
    $safeSku = htmlspecialchars($sku, \ENT_XML1 | \ENT_COMPAT, 'UTF-8');
    $safeLabel = htmlspecialchars(\mb_strimwidth($label, 0, 32, '…'), \ENT_XML1 | \ENT_COMPAT, 'UTF-8');
    $safeTaxon = htmlspecialchars($taxon, \ENT_XML1 | \ENT_COMPAT, 'UTF-8');

    return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">
  <rect width="400" height="300" fill="{$bg}"/>
  <rect x="10" y="10" width="380" height="280" rx="8" fill="none" stroke="{$fg}" stroke-opacity="0.3" stroke-width="1"/>
  <!-- Category strip -->
  <rect x="0" y="230" width="400" height="70" fill="{$fg}" fill-opacity="0.1"/>
  <!-- SKU -->
  <text x="200" y="130"
        font-family="'Helvetica Neue',Arial,sans-serif"
        font-size="22"
        font-weight="700"
        fill="{$fg}"
        text-anchor="middle"
        dominant-baseline="middle">{$safeSku}</text>
  <!-- Product name -->
  <text x="200" y="165"
        font-family="'Helvetica Neue',Arial,sans-serif"
        font-size="13"
        fill="{$fg}"
        fill-opacity="0.85"
        text-anchor="middle"
        dominant-baseline="middle">{$safeLabel}</text>
  <!-- Taxon label -->
  <text x="200" y="258"
        font-family="'Helvetica Neue',Arial,sans-serif"
        font-size="11"
        fill="{$fg}"
        fill-opacity="0.65"
        text-anchor="middle"
        dominant-baseline="middle">{$safeTaxon}</text>
  <!-- Watermark corner -->
  <text x="390" y="292"
        font-family="monospace"
        font-size="9"
        fill="{$fg}"
        fill-opacity="0.3"
        text-anchor="end">demo fixture</text>
</svg>
SVG;
}
