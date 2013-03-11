<?php

namespace Packager;

class RepositorySubject implements \SplSubject
{
    /**
     * @var array
     */
    private $_observers = array();
    
    /**
     *
     * @var Repository
     */
    private $_repository;

    /**
     *
     * @param Repository $repo
     */
    public function __construct(Repository $repo)
    {
        $this->setRepository($repo);
    }
 
    /**
     *
     * @return Repository 
     */
    public function getRepository()
    {
        return $this->_repository;
    }
    
    /**
     *
     * @param Repository $repo 
     */
    protected function setRepository(Repository $repo)
    {
        $this->_repository = $repo;
    }
    
    
    /**
     *
     * @param \SplObserver $obs 
     */
  	public function attach(\SplObserver $obs)
	{
		$this->_observers[spl_object_hash($obs)] = $obs;
	}

    /**
     *
     * @param \SplObserver $obs 
     */
	public function detach(\SplObserver $obs)
	{
		delete($this->_observers[spl_object_hash($obs)]);
	}

    /**
     * 
     */
	public function notify()
	{
		foreach ($this->_observers as $obs) {
			$obs->update($this);
		}
	}    
}
