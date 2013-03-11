<?php

namespace Packager;

/**
 * 
 */
class PackageFile extends Package
{

    protected $name;
    protected $data;

    /**
     *
     * @param type $name 
     */
    public function __construct($name)
    {
        $this->name    = $name;
    }
    
    public function getData()
    {
        if (!isset($this->data)) {
            $it = new \RegexIterator(
                new \RecursiveIteratorIterator(new \PharData($this->name)),
                '#composer.json$#'
            );

            $result = (object) array('name' => null, 'version' => null);

            $it->next();

            if ($it->valid()) {
                $result = json_decode(file_get_contents($it->current()));
                if (!isset($result->version) && ($version = $this->getVersionFromName())) {
                    $result->version = $version;
                }
            }
            
            $this->data = $result;
       }

       return $this->data;
    }

    /**
     *
     * @return type 
     */
    public function getVersion()
    {
        return $this->getData()->version;
    }

    /**
     *
     * @return type 
     */
    public function getName()
    {
        return $this->getData()->name;
    }

    /**
     *
     * @return string 
     */
    public function getType()
    {
        $reg    = null;
        $result = null;
        
        if (preg_match('#(.+).(zip)$#i', $this->name, $reg)) {
            $result = 'zip';
        } else if (preg_match('#(.+).(tgz|tar.gz|tar.bz2)$#i', $this->name, $reg)) {
            $result = 'tar';
        }

        return $result;
    }

    /**
     *
     * @return type 
     */
    protected function getVersionFromName()
    {
        $reg    = null;
        $result = null;
        
        if (preg_match(
            '#(.+)-([0-9a-zA-Z\.]+).(zip|tgz|tar.gz|tar.bz2)$#i', 
            $this->name, 
            $reg
        )) {
            $result = $reg[2];
        }

        return $result;
    }
}