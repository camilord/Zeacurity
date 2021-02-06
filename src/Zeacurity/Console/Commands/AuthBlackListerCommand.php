<?php
/**
 * AlphaOne Building Consent System
 * Copyright 2021
 * Generated in PhpStorm.
 * Developer: Camilo Lozano III - www.camilord.com
 *                              - github.com/camilord
 *                              - linkedin.com/in/camilord
 *
 * Zeacurity - AuthBlackListerCommand.php
 * Username: Camilo
 * Date: 7/02/2021
 * Time: 9:51 AM
 */

namespace camilord\Zeacurity\Console\Commands;


use camilord\utilus\IO\SystemUtilus;
use camilord\utilus\Security\Sanitizer;
use camilord\utilus\String\ValueValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use camilord\Zeacurity\Console\BaseCommand;
use camilord\Zeacurity\Console\CommandInterface;

/**
 * Class AuthBlackListerCommand
 * @package Zeacurity\Console\Commands
 */
class AuthBlackListerCommand extends BaseCommand implements CommandInterface
{
    protected static $defaultName = 'auth_blacklister';

    // configure command
    public function configure()
    {
        parent::configure();
        $this->setName('auth_blacklister')
            // the short description shown while running "php bin/console list"
            ->setDescription('Creates a new user.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to create a user...')
            ->setDefinition([
                new InputOption('log-file', '', InputOption::VALUE_REQUIRED, 'auth log file location'),
                new InputOption('full-scan', '', InputOption::VALUE_OPTIONAL, 'full scan on auth log file or scan the last x lines (param --lines)'),
                new InputOption('lines', '', InputOption::VALUE_OPTIONAL, 'number of lines to be scan if its not full scan'),
            ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $log_file = $input->getOption('log-file');
        $log_file = (stripos($log_file, '/') === false) ? APP_PATH.'/'.$log_file : $log_file;
        $log_file = SystemUtilus::cleanPath($log_file);

        $is_full_scan = ValueValidator::is_value_true($input->getOption('full-scan'));
        $max_lines = (int)Sanitizer::numeric_cleaner($input->getOption('lines'));
        $max_lines = ($max_lines === 0) ? 1000 : $max_lines;

        $output->writeln([
            "Parsing {$log_file} ...",
            '=======================------- - - -  -   -',
            '',
        ]);

        if (!file_exists($log_file)) {
            echo "Error! File not found.\n\n";
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param string $log_file
     * @param bool $is_full
     * @param int $max_lines
     * @return array
     */
    private function getAuthLogData(string $log_file, bool $is_full = false, int $max_lines = 1000): array {
        if ($is_full) {
            $lines = file($log_file);
        } else {
            $cmd = "tail -n {$max_lines} {$log_file}";

            ob_start();
            system($cmd);
            $output = ob_get_contents();
            ob_end_clean();

            $lines = explode($output, "\n");
        }


        $new_lines = [];
        foreach($lines as $line) {
            if (stripos($line, ': Invalid user') !== false) {
                $new_lines[] = $line;
            } else if (stripos($line, 'authentication failure') !== false) {
                $new_lines[] = $line;
            }
        }

        return $new_lines;
    }
}