<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Reporter;

enum SeverityLevel: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}
