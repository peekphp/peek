<?php

namespace Peek\Commands;

use Peek\Config;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'init')]
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('Initialize Peek by setting up an AI client with an API key.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $existingClients = Config::getAllClients();

        $clientsAvailable = ['DeepSeek', 'OpenAI', 'Anthropic', 'Custom'];
        $question = new ChoiceQuestion(
            '<info>Select the AI client to configure:</info>',
            $clientsAvailable,
            0 // Default choice (DeepSeek)
        );
        $clientName = strtolower($helper->ask($input, $output, $question));

        if ($clientName === 'custom') {
            $question = new Question('<info>Enter a custom AI client name:</info> ');
            $clientName = strtolower(trim($helper->ask($input, $output, $question)));

            if ($clientName === '' || $clientName === '0') {
                $output->writeln('<error>No client name entered. Initialization aborted.</error>');

                return Command::FAILURE;
            }
        }

        if (isset($existingClients[$clientName])) {
            $output->writeln("<error>$clientName client already exists in the configuration.</error>");

            return Command::FAILURE;
        }

        $defaultUrls = [
            'DeepSeek' => 'https://api.deepseek.com',
            'OpenAI' => 'https://api.openai.com',
            'qwen' => 'https://dashscope-intl.aliyuncs.com/compatible-mode',
        ];
        $defaultUrl = $defaultUrls[$clientName] ?? 'https://api.example.com';

        $question = new Question("<info>Enter the API URL for $clientName</info> [$defaultUrl]: ", $defaultUrl);
        $apiUrl = trim($helper->ask($input, $output, $question));

        $question = new Question("<info>Enter your API key for $clientName:</info> ");
        $apiKey = trim($helper->ask($input, $output, $question));

        if ($apiKey === '' || $apiKey === '0') {
            $output->writeln("<error>No API key entered for $clientName. Initialization aborted.</error>");

            return Command::FAILURE;
        }

        $question = new Question("<info>Enter the model for $clientName:</info> ");
        $model = trim($helper->ask($input, $output, $question));

        if ($model === '' || $model === '0') {
            $output->writeln("<error>No model entered for $clientName. Initialization aborted.</error>");

            return Command::FAILURE;
        }

        Config::setClientConfig($clientName, $apiKey, $apiUrl, $model);
        $output->writeln("<info>API key for $clientName saved successfully.</info>");

        return Command::SUCCESS;
    }
}
