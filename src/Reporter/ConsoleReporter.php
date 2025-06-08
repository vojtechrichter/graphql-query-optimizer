<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Reporter;

use GraphqlQueryOptimizer\Model\Issue;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ConsoleReporter
{
    public function __construct(
        private SymfonyStyle $out
    ) {
    }

    /**
     * @param array<Issue> $issues
     * @param SeverityLevel $minSeverity
     * @return void
     */
    public function report(array $issues, SeverityLevel $minSeverity = SeverityLevel::Info): void
    {
        $filteredIssues = $this->filterBySeverity($issues,  $minSeverity);

        if (count($filteredIssues) < 1) {
            $this->out->success('No issues found! Your GraphQL query looks optimized.');
            return;
        }

        $this->out->title('GraphQL Query Analysis Results');

        $groupedIssues = $this->groupBySeverity($filteredIssues);

        foreach (array_column(SeverityLevel::cases(), 'value') as $severity) {
            if (!isset($groupedIssues[$severity])) {
                continue;
            }

            $this->reportSeverityGroup($severity, $groupedIssues[$severity]);
        }

        $this->out->newLine();
        $this->printSummary($filteredIssues);
    }

    /**
     * @param array<Issue> $issues
     * @param SeverityLevel $severity
     * @return array<Issue>
     */
    private function filterBySeverity(array $issues, SeverityLevel $severity): array
    {
        return array_filter($issues, function (Issue $issue) use ($severity): bool {
            return $issue->getSeverity() === $severity->value;
        });
    }

    /**
     * @param array<Issue> $issues
     * @return array<string, array<Issue>>
     */
    private function groupBySeverity(array $issues): array
    {
        $grouped = [];
        foreach ($issues as $issue) {
            $grouped[$issue->getSeverity()][] = $issue;
        }

        return $grouped;
    }

    private function reportIssue(Issue $issue, SeverityLevel $severity): void
    {
        $locationString = '';
        if ($issue->getLocation() !== null) {
            $loc = $issue->getLocation();
            $locationString = sprintf(' (line %d, column %d)', $loc->line, $loc->column);
        }

        $symbol = match ($severity->value) {
            SeverityLevel::Error->value => '❌',
            SeverityLevel::Warning->value => '⚠️',
            SeverityLevel::Info->value => 'ℹ'
        };

        $this->out->writeln(sprintf(
            '  %s <comment>%s</comment>',
            $symbol,
            $issue->getTitle(),
            $locationString
        ));

        $this->out->writeln(sprintf('   %s', $issue->getDescription()));
        $this->out->newLine();
    }

    private function reportSeverityGroup(SeverityLevel $severityLevel, array $issues): void
    {
        $title = sprintf('%s (%d)', ucfirst($severityLevel->value), count($issues));

        $this->out->section($title);

        foreach ($issues as $issue) {
            $this->reportIssue($issue, $severityLevel);
        }
    }

    /**
     * @param array<Issue> $issues
     * @return void
     */
    private function printSummary(array $issues): void
    {
        $counts = array_count_values(
            array_map(fn (Issue $issue): string => $issue->getSeverity(), $issues)
        );

        $summary = [];
        foreach (array_column(SeverityLevel::cases(), 'value') as $severity) {
            if (isset($counts[$severity])) {
                $summary[] = sprintf('%d %s', $counts[$severity], $severity);
            }
        }

        $this->out->writeln(sprintf('Found %s', implode(', ', $summary)));
    }
}
