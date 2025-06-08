<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Parser;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;
use GraphQL\Language\Source;

final class QueryParser
{
    public function parse(string $query): DocumentNode
    {
        try {
            return Parser::parse(
                new Source($query)
            );
        } catch (SyntaxError | \JsonException $e) {
            throw new \InvalidArgumentException('Invalid query syntax: ' . $e->getMessage());
        }
    }
}
