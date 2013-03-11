<?php

namespace Packager\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

/**
 * 
 */
class CompileCommand extends Command
{
    /**
     * 
     */
    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('Compile and create Packager phar file')
            ->addOption(
                'filename', 
                'f', 
                InputOption::VALUE_OPTIONAL, 
                'Phar filename', 
                'packager.phar'
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
        $filename = $input->getOption('filename');
        $root     = dirname(dirname(dirname(dirname(__DIR__))));
        
        $output->writeln(
            sprintf("Compile Packager and create <info>%s</info> file", $filename)
        );
        
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in($root);
               
        $phar = new \Phar($filename, 0, $filename);
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();

        $phar->buildFromIterator($finder, $root);
        
        $this->addBin($phar, 'bin/packager', $root.'/bin/packager');
        
        $phar->setStub($this->getStub($filename));

        $phar->stopBuffering();
        $phar->compressFiles(\Phar::GZ);

        unset($phar);
    }
    
    private function addBin($phar, $name, $file)
    {
        $content = file_get_contents($file);
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString($name, $content);
    }    
    
    private function getStub($filename)
    {
        $format = <<<'EOF'
#!/usr/bin/env php
<?php

Phar::mapPhar("%s");

require 'phar://%s/bin/packager';

__HALT_COMPILER();
EOF;
        return sprintf($format, $filename, $filename);
    }    
}