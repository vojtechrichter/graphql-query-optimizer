<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Model;

use GraphQL\Language\SourceLocation;

final class Issue
{
    public function __construct(
        private string $title,
        private string $description,
        private string $severity,
        private ?SourceLocation $location = null
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getLocation(): ?SourceLocation
    {
        return $this->location;
    }
}
