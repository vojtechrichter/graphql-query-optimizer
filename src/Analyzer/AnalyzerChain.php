<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;
use GraphqlQueryOptimizer\Model\Issue;

final class AnalyzerChain
{
    /**
     * @param list<Analyzer> $analyzers
     */
    public function __construct(
        private array $analyzers
    ) {
    }

    public function addAnalyzer(Analyzer $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    /**
     * @param DocumentNode $astRoot
     * @return list<Issue>
     */
    public function analyze(DocumentNode $astRoot): array
    {
        $issues = [];

        foreach ($this->analyzers as $analyzer) {
            $issues = array_merge($issues, $analyzer->analyze($astRoot));
        }

        return $issues;
    }
}
