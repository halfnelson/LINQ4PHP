<?php
namespace LINQ4PHP\Iterators;

abstract class DelayedExecutionIterator extends LinqIterator {
	private $isfirstrun = TRUE;
	protected abstract function firstrun() ;
	
	public function rewind() {
		if ($this->isfirstrun) {
			$this->firstrun();
			$this->isfirstrun = FALSE;
		}
		parent::rewind();
	}
	
	public function next() {
		if ($this->isfirstrun) {
			$this->firstrun();
			$this->isfirstrun = FALSE;
		}
		parent::next();
	}
	
}