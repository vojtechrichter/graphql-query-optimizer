<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Analyzer;

use GraphQL\Language\AST\DocumentNode;
use GraphqlQueryOptimizer\Model\Issue;

abstract class Analyzer
{
    /**
     * @param DocumentNode $astRoot
     * @return list<Issue>
     */
    abstract public function analyze(DocumentNode $astRoot): array;

    public function getName(): string
    {
        return self::class;
    }
}
