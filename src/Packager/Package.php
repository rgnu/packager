<?php

namespace Packager;

/**
 * 
 */
abstract class Package {

    protected $name;
    
    /**
     *
     * @param type $name 
     */
    public function __construct($name)
    {
        $this->name    = $name;
    }

    /**
     * 
     */
    abstract public function getName();
    
    /**
     * 
     */
    abstract public function getVersion();
    
    /**
     * 
     */
    abstract public function getData();

}