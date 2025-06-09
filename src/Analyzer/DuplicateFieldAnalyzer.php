<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\SelectionSetNode;
use GraphQL\Language\Visitor;
use GraphqlQueryOptimizer\Model\Issue;
use GraphqlQueryOptimizer\Reporter\SeverityLevel;

final class DuplicateFieldAnalyzer extends Analyzer
{
    public function analyze(DocumentNode $astRoot): array
    {
        $issues = [];
        $fieldCounts = [];

        Visitor::visit($astRoot, [
            'SelectionSet' => [
                'enter' => function (SelectionSetNode $node) use (&$fieldCounts, &$issues) {
                    $localFields = [];

                    foreach ($node->selections as $selection) {
                        if ($selection instanceof FieldNode) {
                            $fieldName = $selection->name->value;

                            if (!isset($localFields[$fieldName])) {
                                $localFields[$fieldName] = [];
                            }

                            $localFields[$fieldName][] = $selection;
                        }
                    }

                    foreach ($localFields as $fieldName => $occurrences) {
                        if (count($occurrences) > 1) {
                            $issues[] = new Issue(
                                $this->getName(),
                                sprintf(
                                    'Field "%s" is selected %d times in the same selection set. This is redundant.',
                                    $fieldName,
                                    count($occurrences)
                                ),
                                SeverityLevel::Warning->value,
                                $occurrences[0]->loc
                            );
                        }
                    }
                }
            ]
        ]);

        return $issues;
    }
}
