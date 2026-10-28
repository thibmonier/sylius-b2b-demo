<?php

declare(strict_types=1);

namespace App\Tests\Support\Supply;

/**
 * US-208: hardening rules of a GitHub Actions workflow, read line by line so that a violation names the file, the line
 * and the rule ("ci.yaml:36 persist-credentials: ..."). The same rules back the test of the repository and the tests
 * that feed it faulty workflows.
 */
final class WorkflowRules
{
    /** @return list<string> one "file:line rule: explanation" entry per violation */
    public static function violations(string $file, string $yaml): array
    {
        $lines = explode("\n", $yaml);

        return [
            ...self::workflowPermissions($file, $lines),
            ...self::jobPermissions($file, $lines),
            ...self::checkoutCredentials($file, $lines),
            ...self::forbiddenTrigger($file, $lines),
        ];
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private static function workflowPermissions(string $file, array $lines): array
    {
        foreach ($lines as $index => $line) {
            if (1 === preg_match('/^permissions:/', $line)) {
                return 1 === preg_match('/^permissions:\s*\{\}\s*(#.*)?$/', $line)
                    ? []
                    : [sprintf('%s:%d workflow-permissions: the workflow level must be "permissions: {}"', $file, $index + 1)];
            }
        }

        return [sprintf('%s:1 workflow-permissions: the workflow level must be "permissions: {}" (key missing)', $file)];
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private static function jobPermissions(string $file, array $lines): array
    {
        $violations = [];
        $jobsLine = array_keys(array_filter($lines, static fn (string $line): bool => 1 === preg_match('/^jobs:/', $line)))[0] ?? null;
        if (null === $jobsLine) {
            return [];
        }
        $jobs = array_keys(array_filter(
            $lines,
            static fn (string $line, int $index): bool => $index > $jobsLine && 1 === preg_match('/^ {4}[\w-]+:\s*$/', $line),
            \ARRAY_FILTER_USE_BOTH,
        ));
        foreach ($jobs as $position => $start) {
            $end = $jobs[$position + 1] ?? \count($lines);
            $block = implode("\n", \array_slice($lines, $start, $end - $start));
            if (1 !== preg_match('/^ {8}permissions:\s*\n {12}contents: read\s*$/m', $block)) {
                $violations[] = sprintf('%s:%d job-permissions: job "%s" must declare "permissions: contents: read"', $file, $start + 1, trim($lines[$start], " :\t"));
            }
        }

        return $violations;
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private static function checkoutCredentials(string $file, array $lines): array
    {
        $violations = [];
        foreach ($lines as $index => $line) {
            if (1 !== preg_match('/^(\s*)(- )?uses:\s*actions\/checkout@/', $line, $match)) {
                continue;
            }
            $step = self::stepOf($lines, $index, \strlen($match[1]) + (isset($match[2]) ? 2 : 0));
            if (1 !== preg_match('/^\s*persist-credentials:\s*false\s*(#.*)?$/m', $step)) {
                $violations[] = sprintf('%s:%d persist-credentials: actions/checkout must set "persist-credentials: false"', $file, $index + 1);
            }
        }

        return $violations;
    }

    /**
     * @param list<string> $lines
     *
     * @return list<string>
     */
    private static function forbiddenTrigger(string $file, array $lines): array
    {
        $violations = [];
        foreach ($lines as $index => $line) {
            if (1 === preg_match('/^[^#]*\bpull_request_target\b/', $line)) {
                $violations[] = sprintf('%s:%d no-pull-request-target: the pull_request_target trigger is forbidden', $file, $index + 1);
            }
        }

        return $violations;
    }

    /**
     * The text of the step that holds the line: from its "- " to the next line at the same or a lower indentation.
     *
     * @param list<string> $lines
     */
    private static function stepOf(array $lines, int $index, int $keyIndent): string
    {
        $start = $index;
        while ($start > 0 && 1 !== preg_match('/^ {' . max(0, $keyIndent - 2) . '}- /', $lines[$start])) {
            --$start;
        }
        $end = $index + 1;
        while ($end < \count($lines) && ('' === trim($lines[$end]) || \strlen($lines[$end]) - \strlen(ltrim($lines[$end])) >= $keyIndent)) {
            ++$end;
        }

        return implode("\n", \array_slice($lines, $start, $end - $start));
    }
}
