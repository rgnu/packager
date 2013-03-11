<?php

/*
 * This file is part of Packager.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Packager\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class Application extends BaseApplication
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Packager', '1.0.0');
        $this->setCatchExceptions(true);
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        return parent::doRun($input, $output);
    }

    /**
     * Initializes all the composer commands
     */
    protected function registerCommands()
    {
        $this->add(new Command\BuildRepositoryCommand());
        $this->add(new Command\CompileCommand());
        $this->add(new Command\DeployCommand());
        $this->add(new Command\Deploy\CloudFileCommand());
    }
}
