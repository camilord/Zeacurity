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

use camilord\utilus\Data\ArrayUtilus;
use camilord\utilus\IO\ConsoleUtilus;
use camilord\utilus\IO\SystemUtilus;
use camilord\Zeacurity\DataQuery\BlackListDataQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use camilord\Zeacurity\Console\BaseCommand;
use camilord\Zeacurity\Console\CommandInterface;

/**
 * Class appendBlackListToIpTablesCommand
 * @package Zeacurity\Console\Commands
 */
class AppendBlackListToIpTablesCommand extends BaseCommand implements CommandInterface
{
    const IP_RANGE = 0;
    const IP_ADDRESS = 1;

    /**
     * @var string
     */
    protected static $defaultName = 'append_blacklister';

    /**
     * configure command
     */
    public function configure()
    {
        parent::configure();
        $this->setName('append_blacklister')
            // the short description shown while running "php bin/console list"
            ->setDescription('Generate new firewall')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Generate new firewall')
            ->setDefinition([
                new InputOption('firewall-file', '', InputOption::VALUE_REQUIRED, 'firewall you want to append the blacklist'),
                new InputOption('max', '', InputOption::VALUE_OPTIONAL, 'max numbers IP from the database'),
                new InputOption('mode', '', InputOption::VALUE_OPTIONAL, 'block IP address or IP ranges')
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $firewall_file = $input->getOption('firewall-file');
        $max_rows = (int)$input->getOption('max');
        $block_mode = (int)$input->getOption('mode');
        $firewall_file = SystemUtilus::cleanPath($firewall_file);

        if (!file_exists($firewall_file)) {
            echo "Error! Firewall not found.\n\n";
            return Command::FAILURE;
        }

        $max_rows = ($max_rows === 0) ? 1500 : $max_rows;

        $output->writeln([
            "Firewall File: {$firewall_file}",
            '=======================------- - - -  -   -',
            '',
        ]);
        $dq = new BlackListDataQuery($this->getDb());
        $data = $dq->get_list($max_rows);

        echo "Loading data to cache: ";
        $blacklist = "";
        $ctr = 0;
        $total = count($data);
        if (ArrayUtilus::haveData($data))
        {
            if ($block_mode === self::IP_ADDRESS) {
                $template = "/sbin/iptables -A INPUT -p tcp -s {IP} --dport 22 -j DROP -w";
            } else {
                $template = "/sbin/iptables -A INPUT -s {IP} -j DROP -w";
            }

            foreach($data as $item)
            {
                $ctr++;
                ConsoleUtilus::show_status($ctr, $total);

                // convert IP address to IP block
                $ip_address = isset($item['ip']) ? trim($item['ip']) : false;
                $ip_block = false;
                if ($ip_address && $block_mode === self::IP_ADDRESS) {
                    $ip_block = $ip_address.'/32';
                } else if ($ip_address) {
                    $ip_block = $this->convert2block_24($ip_address);
                }

                // check if its already been added to list
                if (stripos($blacklist, $ip_block) !== false || !$ip_block) {
                    continue;
                }

                // add it to the list
                $blacklist .= ($blacklist === "") ?
                    str_replace("{IP}", $ip_block, $template) :
                    "\n".str_replace("{IP}", $ip_block, $template);
            }
        }
        echo "\n";

        if (strlen($blacklist) > 5) {
            // create backup first...
            echo "Creating backup of your firewall ... ";
            $firewall_template_file = APP_PATH.'/'.str_replace(".sh", "_template.sh", basename($firewall_file));
            if (!file_exists($firewall_template_file)) {
                copy($firewall_file, $firewall_template_file);
            }
            $backup_file = APP_PATH.'/backups/'.str_replace(".sh", "_".date('Ymd').".sh", basename($firewall_file));
            if (!file_exists($backup_file)) {
                copy($firewall_file, $backup_file);
            }
            echo "\t -> OK\n";

            echo "Generating new firewall script ... ";
            $firewall_content = file_get_contents($firewall_template_file);
            $firewall_content = str_replace("# {SSH_BLOCK_IPS}", $blacklist, $firewall_content);
            file_put_contents($firewall_file, $firewall_content);
            echo "\t -> OK\n";

            echo "Changing permission ... ";
            system("chmod u+x {$firewall_file}");
            echo "\t -> OK\n";
            echo "Executing firewall script ... ";
            system("bash {$firewall_file}");
            echo "\t -> OK\n";
        } else{
            echo "\nNothing to update as of the moment.";
        }

        return Command::SUCCESS;
    }

    /**
     * @param $ip
     * @return false
     */
    private function convert2block_24($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $tmp = explode('.', trim($ip));
            array_pop($tmp);
            $tmp[] = 0;

            return implode('.', $tmp).'/24';
        }
        return false;
    }
}