<?php

namespace Packager;

use Symfony\Component\Finder\Finder;

/**
 * 
 */
class Repository
{

    protected $homepage;
    protected $repository;
    protected $addPackageSubject;

    /**
     *
     * @param string $homepage 
     */
    public function __construct($homepage, $addPackageSubject = null)
    {
        $this->homepage             = $homepage;
        $this->repository           = new \stdClass();
        $this->repository->packages = array();

        if (!isset($addPackageSubject)) {
            $addPackageSubject = new RepositorySubject($this);
        }
        $this->setAddPackageSubject($addPackageSubject);
    }

    /**
     *
     * @return RepositorySubject
     */
    public function getAddPackageSubject()
    {
        return $this->addPackageSubject;
    }

    /**
     *
     * @param RepositorySubject $subject
     * 
     * @return void
     */
    protected function setAddPackageSubject(RepositorySubject $subject = null)
    {
        $this->addPackageSubject = $subject;
    }

    /**
     *
     * @param Package $package
     * @throws InvalidArgumentException 
     */
    public function addPackage(Package $package)
    {
        $name    = $package->getName();
        $version = $package->getVersion();
        $data    = (object) $package->getData();
        $subject = $this->getAddPackageSubject();

        if (empty($name))
            throw new \InvalidArgumentException('Unable to add a package without name');
        if (empty($version))
            throw new \InvalidArgumentException('Unable to add a package without version');
        if (!isset($data->dist) && !isset($data->source))
            throw new \InvalidArgumentException('Unable to add a package without dist/source');

        $this->repository->packages[$name][$version] = $data;

        $subject->package   = $package;
        $subject->exception = null;
        $subject->notify();
    }
    
    /**
     * 
     * @param mixed $repo
     * 
     * @return void
     */
    public function process($repo)
    {
        if ($repo->type == "dir") {
            $this->processFromDir($repo);
        } else if ($repo->type == "composer") {
            $this->processFromComposer($repo);
        }
    }

    /**
     *
     * @param mixed $data
     * @param mixed $filter 
     * 
     * @return void
     */
    public function processFromDir($data)
    {
        if (!isset($data->filter)) {
            $data->filter = '/(zip|tgz|tar.gz|tar.bz2)$/';
        }
        
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->in($data->path)
            ->name($data->filter)
            ->sortByName();

        $subject = $this->getAddPackageSubject();

        foreach ($finder as $f) {
            try {
                $package                  = new PackageFile($f);
                $url                      = $this->homepage . substr($f, 2);
                $package->getData()->dist = (object) array(
                        'url'  => $url, 'type' => $package->getType()
                );
                $this->addPackage($package);
            } catch (\Exception $e) {
                $subject->package   = $package;
                $subject->exception = $e;
                $subject->notify();
            }
        }
    }

    /**
     *
     * @param mixed $data
     * 
     * @return void
     */
    public function processFromComposer($data)
    {
        $composerLock = $this->openJsonFile($data->path.'/composer.lock');        
        $it           = $composerLock->packages;
        $subject      = $this->getAddPackageSubject();

        foreach ($it as $v) {
            try {
                $packageName = str_replace('/', '-', sprintf(
                    "%s/%s.%s", $v->name, $v->version, $v->dist->type
                ));
                
                $packageName = sprintf("%s/%s", $data->packageDir, $packageName);
                
                if (!file_exists($packageName)) {            
                    copy($v->dist->url, $packageName);
                }

            } catch (\Exception $e) {
                $subject->package   = new PackageFile($packageName);
                $subject->exception = $e;
                $subject->notify();
            }
        }
    }
    
    /**
     * 
     */
    protected function openJsonFile($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \ErrorException(
                sprintf("\"%s\" not exists or can't be reading", $file)
            );            
        }
        
        $result = json_decode(@file_get_contents($file));
        if (!isset($result) || $err = json_last_error()) {
            throw new \ErrorException(
                sprintf("JSON error %d on file \"%s\"", $err, $file)
            );
        }
        
        return $result;
    }

    /**
     * 
     */
    public function toJson()
    {
        return json_encode($this->repository);
    }

    /**
     * 
     */
    public function toFile($fileName)
    {
        file_put_contents($fileName, $this->toJson());
    }

}