<?php

namespace Peek\Commands;

use Peek\Client;
use Peek\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(name: 'peek')]
class PeekCommand extends Command
{
    private ?Client $client = null;

    public function __construct()
    {
        parent::__construct();

        $this->client = $this->initializeClient();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Analyse a specific file or a snippet of code using the client.')
            ->addArgument('file', InputArgument::REQUIRED, 'The path to the file to analyse.')
            ->addOption(
                'lines',
                'l',
                InputOption::VALUE_REQUIRED,
                'The range of lines to analyse in the format "start:end".'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (! $this->client instanceof Client) {
            $output->writeln('<error>No valid API keys found in configuration. Run "peek init" to configure.</error>');

            return Command::FAILURE;
        }

        $filePath = $input->getArgument('file');

        if (! file_exists($filePath)) {
            $output->writeln("<error>The file at path '$filePath' does not exist.</error>");

            return Command::FAILURE;
        }

        $fileContent = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($fileContent === false) {
            $output->writeln("<error>Failed to read the file at path '$filePath'.</error>");

            return Command::FAILURE;
        }

        $linesOption = $input->getOption('lines');

        try {
            if ($linesOption) {
                $contentToAnalyse = $this->extractSnippet($fileContent, $linesOption);
                $output->writeln("<info>Analyzing snippet from lines $linesOption:</info>");
            } else {
                $contentToAnalyse = implode(PHP_EOL, $fileContent);
                $output->writeln('<info>Analyzing the entire file:</info>');
            }
        } catch (\RuntimeException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        try {
            $response = $this->client->ask($contentToAnalyse);

            $output->writeln('<info>Analysis Result:</info>');
            $this->displayFormattedResponse($output, $response);
        } catch (\RuntimeException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function initializeClient(): ?Client
    {
        $clients = Config::getAllClients();

        if ($clients === []) {
            return null;
        }

        foreach ($clients as $clientData) {
            if (! empty($clientData['api_key']) && ! empty($clientData['url']) && ! empty($clientData['model'])) {
                return new Client($clientData['api_key'], $clientData['url'], $clientData['model']);
            }
        }

        return null;
    }

    private function extractSnippet(array $fileContent, string $linesOption): string
    {
        if (in_array(preg_match('/^(\d+):(\d+)$/', $linesOption, $matches), [0, false], true)) {
            throw new \RuntimeException('Invalid lines format. Use "start:end" (e.g., 5:10).');
        }

        [$start, $end] = [(int) $matches[1], (int) $matches[2]];

        if ($start < 1 || $end < $start || $end > count($fileContent)) {
            throw new \RuntimeException("Invalid line range: $start to $end.");
        }

        return implode(PHP_EOL, array_slice($fileContent, $start - 1, $end - $start + 1));
    }

    private function displayFormattedResponse(OutputInterface $output, string $response): void
    {
        $formattedContent = html_entity_decode($response, ENT_QUOTES, 'UTF-8');
        $formattedContent = preg_replace_callback('/```php\n(.*?)\n```/s',
            fn (array $matches): string => '<fg=yellow>'.trim((string) $matches[1]).'</fg=yellow>',
            $formattedContent
        );

        $lines = explode("\n", (string) $formattedContent);

        foreach ($lines as $line) {
            if (str_contains($line, '**')) {
                $output->writeln('<fg=blue>'.trim($line).'</fg=blue>');
            } elseif (str_contains($line, '###')) {
                $output->writeln('<comment>'.trim($line).'</comment>');
            } else {
                $output->writeln(trim($line));
            }
        }
    }
}
