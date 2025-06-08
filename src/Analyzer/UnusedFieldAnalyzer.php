<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;

final class UnusedFieldAnalyzer extends Analyzer
{
    public function analyze(DocumentNode $astRoot): array
    {
        $issues = [];
        $scalarFields = [];

        return $issues;
    }

    private function isCommonlyUnusedField(string $fieldName): bool
    {
        $commonlyUnunsedFieldNames = [
            'id', 'createdAt', 'updatedAt', 'version', 'metadata'
        ];

        return in_array($fieldName, $commonlyUnunsedFieldNames, true);
    }
}
