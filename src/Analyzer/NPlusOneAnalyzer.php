<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\Visitor;
use GraphqlQueryOptimizer\Model\Issue;

final class NPlusOneAnalyzer extends Analyzer
{
    public function analyze(DocumentNode $astRoot): array
    {
        $issues = [];
        $nestedLists = [];

        try {
            Visitor::visit($astRoot, [
                'Field' => [
                    'enter' => function (FieldNode $node) use (&$nestedLists) {
                        // TODO: check against schema

                        if ($this->isListField($node) && $this->hasNestedFields($node)) {
                            $nestedLists[] = [
                                'field' => $node->name->value,
                                'location' => $node->loc,
                                'depth' => $this->calculateDepth($node)
                            ];
                        }
                    }
                ]
            ]);
        } catch (\Exception $e) {
            print_r('AST traversing error: ' . $e->getMessage());
        }

        foreach ($nestedLists as $listField) {
            if ($listField['depth'] > 2) {
                $issues[] = new Issue(
                    $this->getName(),
                    sprintf(
                        'Potential N+1 query detected in field "%s". Consider using DataLoader or batch loading',
                        $listField['field']
                    ),
                    'warning',
                    $listField['location']
                );
            }
        }
    }

    private function calculateDepth(FieldNode $node, int $currentDepth = 1): int
    {
        if ($node->selectionSet === null) {
            return $currentDepth;
        }

        $maxDepth = $currentDepth;
        foreach ($node->selectionSet->selections as $selection) {
            assert($selection instanceof FieldNode);

            $depth = $this->calculateDepth($selection, ++$currentDepth);
            $maxDepth = max($maxDepth, $depth);
        }

        return $maxDepth;
    }

    private function hasNestedFields(FieldNode $node): bool
    {
        return $node->selectionSet !== null &&
            count($node->selectionSet->selections) > 0;
    }

    private function isListField(FieldNode $node): bool
    {
        // TODO: check against schema

        $fieldName = $node->name->value;
        return str_ends_with($fieldName, 's') ||
            str_ends_with($fieldName, 'list') ||
            str_ends_with($fieldName, 'all');
    }
}
