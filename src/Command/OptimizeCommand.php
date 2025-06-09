<?php

declare(strict_types=1);

namespace GraphqlQueryOptimizer\Command;

use GraphqlQueryOptimizer\Analyzer\AnalyzerChain;
use GraphqlQueryOptimizer\Analyzer\DuplicateFieldAnalyzer;
use GraphqlQueryOptimizer\Analyzer\NPlusOneAnalyzer;
use GraphqlQueryOptimizer\Analyzer\UnusedFieldAnalyzer;
use GraphqlQueryOptimizer\Model\Issue;
use GraphqlQueryOptimizer\Parser\QueryParser;
use GraphqlQueryOptimizer\Reporter\ConsoleReporter;
use GraphqlQueryOptimizer\Reporter\SeverityLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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
        $out = new SymfonyStyle($input, $output);
        $queryFile = $input->getArgument('query');

        if (!file_exists($queryFile)) {
            $out->error('Query file not found: ' . $queryFile);

            return Command::FAILURE;
        }

        try {
            $queryContent = file_get_contents($queryFile);
            if ($queryContent === false) {
                $out->error('Unable to read query file: ' . $queryFile);

                return Command::FAILURE;
            }

            $parser = new QueryParser();
            $ast = $parser->parse($queryContent);

            $analyzerChain = new AnalyzerChain([
                new NPlusOneAnalyzer(),
                new DuplicateFieldAnalyzer(),
                new UnusedFieldAnalyzer()
            ]);

            $issues = $analyzerChain->analyze($ast);

            $reporter = new ConsoleReporter($out);
            $reporter->report($issues);

            $hasErrors = count(array_filter($issues, fn (Issue $issue): bool => $issue->getSeverity() === SeverityLevel::Error->value)) > 0;

            return $hasErrors ? Command::FAILURE : Command::SUCCESS;
        } catch (\Exception $e) {
            $out->error('Error analyzing query: ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
