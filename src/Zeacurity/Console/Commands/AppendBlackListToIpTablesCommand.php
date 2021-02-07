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
 * Class appendBlackListToIpTablesCommand
 * @package Zeacurity\Console\Commands
 */
class AppendBlackListToIpTablesCommand extends BaseCommand implements CommandInterface
{
    protected static $defaultName = 'append_blacklister';

    // configure command
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
                new InputOption('firewall-file', '', InputOption::VALUE_REQUIRED, 'firewall you want to append the blacklist')
            ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $firewall_file = $input->getOption('firewall-file');
        $firewall_file = SystemUtilus::cleanPath($firewall_file);

        if (!file_exists($firewall_file)) {
            echo "Error! Firewall not found.\n\n";
            return Command::FAILURE;
        }

        $output->writeln([
            "Firewall File: {$firewall_file}",
            '=======================------- - - -  -   -',
            '',
        ]);
        $dq = new BlackListDataQuery($this->getDb());
        $data = $dq->get_list();

        echo "Loading data to cache: ";
        $blacklist = "";
        $ctr = 0;
        $total = count($data);
        if (ArrayUtilus::haveData($data))
        {
            $template = "/sbin/iptables -A INPUT -s {IP} -j DROP";

            foreach($data as $item)
            {
                $ctr++;
                ConsoleUtilus::show_status($ctr, $total);

                $ip = $item['ip'];
                $blacklist .= ($blacklist === "") ?
                    str_replace("{IP}", $ip, $template) :
                    "\n".str_replace("{IP}", $ip, $template);
            }
        }
        echo "\n";

        // create backup first...
        echo "Creating backup of your firewall ... ";
        $firewall_template_file = APP_PATH.'/'.str_replace(".sh", "_template.sh", basename($firewall_file));
        if (!file_exists($firewall_template_file)) {
            copy($firewall_file, $firewall_template_file);
        }
        $backup_file = APP_PATH.'/backups/'.str_replace(".sh", "_".date('YmdHis').".sh", basename($firewall_file));
        copy($firewall_file, $backup_file);
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

        return Command::SUCCESS;
    }
}