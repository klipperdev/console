<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('init:klipper')
            ->setAliases(['init'])
            ->setDescription('Init klipper')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $commands = [];
        $commandNames = [];
        $executedCommands = [];

        foreach ($this->getApplication()->all('init') as $command) {
            if ($this->getName() !== ($name = $command->getName())) {
                $commands[$name] = $command;
                $commandNames[] = $name;
            }
        }

        while (!empty($commands)) {
            foreach ($commands as $name => $command) {
                $requiredCommands = $command instanceof RequiredCommandsInterface
                    ? $command->getRequiredCommands()
                    : [];

                foreach ($requiredCommands as $requiredCommand) {
                    $isOptional = 0 === strpos($requiredCommand, '?');
                    $requiredCommand = ltrim($requiredCommand, '?');

                    if (!$isOptional && !\in_array($requiredCommand, $commandNames, true)) {
                        throw new RuntimeException(sprintf('The "%s" command is required', $requiredCommand));
                    }
                }

                if (empty($requiredCommands) || empty(array_diff($requiredCommands, $executedCommands))) {
                    $this->runCommand($output, $command);
                    $executedCommands[] = $name;
                    unset($commands[$name]);

                    break;
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function runCommand(OutputInterface $output, Command $commandInstance): void
    {
        $output->writeln('* <info>'.$commandInstance->getName().'</info>');

        $arguments = [
            'command' => $commandInstance,
        ];
        $input = new ArrayInput($arguments);

        $commandInstance->run($input, $output);
        $output->writeln('');
    }
}
