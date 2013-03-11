<?php

namespace Packager;

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
    public function __construct ($homepage, $addPackageSubject=null)
    {
        $this->homepage   = $homepage;
        $this->repository = new \stdClass();
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
    protected function setAddPackageSubject(RepositorySubject $subject=null)
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
     * @param type $dir
     * @param type $filter 
     */
    public function loadFromDir($dir, $filter='#/.+(zip|tgz|tar.gz|tar.bz2)$#i')
    {
        $it = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir)
            ),
            $filter
        );
        
        $subject = $this->getAddPackageSubject();

        foreach($it as $v) {
            try {
                $package = new PackageFile($v);
                $url = $this->homepage . substr($v, strlen($dir));
                $package->getData()->dist = (object) array(
                    'url' => $url, 'type' => $package->getType()
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