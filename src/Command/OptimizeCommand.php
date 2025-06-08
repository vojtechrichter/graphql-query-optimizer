<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class OptimizeCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('optimize')
            ->setDescription('Optimize GraphQL queries')
            ->addArgument('query', InputArgument::REQUIRED, 'Query file path to be optimized');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
