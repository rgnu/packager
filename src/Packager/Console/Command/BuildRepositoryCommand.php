<?php

namespace Packager\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Packager\Repository;
use Packager\RepositoryListener;

class BuildRepositoryCommand extends Command
{
    /**
     * 
     */
    protected function configure()
    {
        $this
            ->setName('build:repository')
            ->setDescription('Build a composer repository')
            ->addOption(
                'config', 
                'c', 
                InputOption::VALUE_OPTIONAL, 
                'Packager config', 
                './packager.json'
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
        $config = $input->getOption('config');
        
        $output->writeln(sprintf("Loading Config <info>%s</info>", $config));
        
        $config = $this->loadConfig($config);
        
        $repo     = new Repository($config->homepage);
        
        $listener = new RepositoryListener(function($sub) use ($output) {
            
            if (isset($sub->exception)) {
                $output->writeln(sprintf(
                    "<error>Error: %s</error>",
                    $sub->exception->getMessage()
                ));
            } else {
                $output->writeln(
                    sprintf(
                        "Add package <info>%s</info> <comment>(%s)</comment>",
                        $sub->package->getName(), 
                        $sub->package->getVersion()
                    )
                );
            }

        });
        
        $repo->getAddPackageSubject()->attach($listener);

        foreach($config->repositories as $v) {
            $output->writeln(sprintf("Process Repository <info>%s</info>", $v->path));
            if ($v->type == "dir") {
                $repo->loadFromdir($v->path);
            }
        }

        $repo->toFile(sprintf("%s/packages.json", $config->output));
    }
    
    
    /**
     *
     * @param string $fileName
     * 
     * @return stdClass
     */
    protected function loadConfig($fileName)
    {
        $file = new \SplFileInfo($fileName);
        
        if (!$file->isReadable())
            throw new \InvalidArgumentException(
                sprintf("File \"%s\" can't be read", $fileName)
            );

        $result = file_get_contents($fileName);
        
        $result = json_decode($result);
        $err    = json_last_error();
        
        if ($err) 
            throw new \ErrorException(
                sprintf("JSON error %d on file \"%s\"", $err, $fileName)
            );

        return $result;
    }
}