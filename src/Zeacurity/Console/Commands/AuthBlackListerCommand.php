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


use camilord\utilus\IO\ConsoleUtilus;
use camilord\utilus\IO\SystemUtilus;
use camilord\utilus\Security\Sanitizer;
use camilord\utilus\String\ValueValidator;
use camilord\Zeacurity\DataQuery\BlackListDataQuery;
use camilord\Zeacurity\Utils\WhitelistIPsUtil;
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
    const REGEX_IP = '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/i';

    protected static $defaultName = 'auth_blacklister';
    protected static $cache_ips = [];

    // configure command
    public function configure()
    {
        parent::configure();
        $this->setName('auth_blacklister')
            ->setDescription('Process auth.log and store the database')
            ->setHelp('Process auth.log and store the database')
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
            "Log File: {$log_file}",
            '=======================------- - - -  -   -',
            '',
        ]);
        echo "Parsing data from {$log_file} ...\n";
        $lines = $this->getAuthLogData($log_file, $is_full_scan, $max_lines);
        echo "\nProcessing data ...\n";
        $this->processLogLines($lines);
        echo "\n";

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

            $lines = explode("\n", $output);
        }

        $total = count($lines);
        $ctr = 0;
        $new_lines = [];
        foreach($lines as $line) {
            $ctr++;
            ConsoleUtilus::show_status($ctr, $total);
            if (stripos($line, ': Invalid user') !== false) {
                $new_lines[] = $line;
            } else if (stripos($line, 'authentication failure') !== false) {
                $new_lines[] = $line;
            } else if (stripos($line, 'invalid user') !== false) {
                $new_lines[] = $line;
            } else if (stripos($line, 'Unable to negotiate') !== false) {
                $new_lines[] = $line;
            /*} else if (preg_match(self::REGEX_IP, $line)) {
                $new_lines[] = $line;*/
            }

        }

        return $new_lines;
    }

    /**
     * @param array $lines
     */
    private function processLogLines(array $lines)
    {
        static $cache_ips = [];
        $dq = new BlackListDataQuery($this->getDb());

        $total_added = 0;
        $total = count($lines);
        $ctr = 0;
        foreach($lines as $line)
        {
            $ctr++;
            ConsoleUtilus::show_status($ctr, $total);
            if (preg_match_all(self::REGEX_IP, $line, $matches))
            {
                foreach($matches as $item)
                {
                    $ip = trim($item[0]);
                    if (
                        filter_var($ip, FILTER_VALIDATE_IP) &&
                        !in_array($ip, $cache_ips) &&
                        !$dq->exists($ip) &&
                        !in_array($ip, WhitelistIPsUtil::getList())
                    ) {
                        $dq->add($ip, $line);
                        $cache_ips[] = $ip;
                        $total_added++;
                    }
                }
            }
        }

        echo "\nTotal new IPs blocked: {$total_added}\n";
    }
}