<?php

namespace Packager;

/**
 * 
 */
class RepositoryListener implements \SplObserver
{
    private $_function;
    
    /**
     *
     * @param Closure $function 
     */
    public function __construct(\Closure $function)
    {
      $this->_function = $function;
    }
    
    /**
     *
     * @param \SplSubject $obj
     */
	public function update(\SplSubject $obj)
	{
		$this->_function->__invoke($obj);
	}
}
