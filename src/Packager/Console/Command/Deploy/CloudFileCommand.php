<?php

namespace Packager\Console\Command\Deploy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;

/**
 * 
 */
class CloudFileCommand extends Command
{
    /**
     * 
     */
    protected function configure()
    {
        $this
            ->setName('deploy:cloudfile')
            ->setDescription('Deploy the current repository to RackSpace CloudFile')
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
        
        $output->writeln(
            sprintf(
                "Deploy to <info>%s@%s%s</info>", 
                $dest->user, 
                $dest->host,
                $dest->path
            )
        );
        
        $auth = new \CF_Authentication($dest->user, $dest->pass);
        $auth->authenticate();
        
        $conn = new \CF_Connection($auth);
        $cont = $conn->get_container($dest->host);
        
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->in($source->path);

        foreach ($finder as $f) {
            $output->write(sprintf("Upload %s", $f->getRelativePathname()));
            
            try {
                $o = $cont->get_object(substr($dest->path, 1).'/'.$f->getRelativePathname());
            } catch (\NoSuchObjectException $e) {
                $o = $cont->create_object(substr($dest->path, 1).'/'.$f->getRelativePathname());
            }
            
            if ($o->getETag() == hash_file('md5', $f)) {
                $output->writeln(" <comment>Synced</comment>");
            } else {
                $o->content_type = 'binary/octet-stream';
                $o->load_from_filename($f);

                $output->writeln(" <info>Uploaded</info>");
            }
        }
    }
}