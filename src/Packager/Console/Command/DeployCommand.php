<?php

namespace Packager\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 
 */
class DeployCommand extends Command
{
    /**
     * 
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy the current repository')
            ->addOption(
                'dest', 'd', InputOption::VALUE_REQUIRED, 'Destination Uri (ex: cloudfile://test:123@container/test)'
            )
            ->addOption(
                'source', 's', InputOption::VALUE_REQUIRED, 'Source Uri (ex:./)', './'
            )            
        ;
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dest   = $input->getOption('dest');
        $source = $input->getOption('source');
        
        $source = (object) parse_url($source);
        $dest   = (object) parse_url($dest);
        
        $comand = $this->getApplication()->find(sprintf("deploy:%s", $dest->scheme));
        
        $comand->run($input, $output);
   }
}