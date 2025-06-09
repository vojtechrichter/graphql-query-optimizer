<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Visitor;
use GraphqlQueryOptimizer\Model\Issue;
use GraphqlQueryOptimizer\Reporter\SeverityLevel;

final class UnusedFieldAnalyzer extends Analyzer
{
    public function analyze(DocumentNode $astRoot): array
    {
        $issues = [];
        $scalarFields = [];

        Visitor::visit($astRoot, [
            'Field' => [
                'enter' => function (FieldNode $node) use (&$scalarFields): void {
                    // TODO: not certain
                    if ($node->selectionSet === null) {
                        $scalarFields[] = [
                            'name' => $node->name->value,
                            'location' => $node->loc
                        ];
                    }
                }
            ]
        ]);

        // TODO: check against schema
        foreach ($scalarFields as $field) {
            if ($this->isCommonlyUnusedField($field['name'])) {
                $issues[] = new Issue(
                    $this->getName(),
                    sprintf(
                        'Field "%s" might be unnecessary. Consider if this data is actually used.',
                        $field['name']
                    ),
                    SeverityLevel::Info->value,
                    $field['location']
                );
            }
        }

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
