<?php
namespace LINQ4PHP\Iterators;

class ZipIterator extends \IteratorIterator {
	private $resultfunc;
	private $it2;
	
	public function __construct($it1,$it2,$resultfunc) {
		$this->resultfunc = $resultfunc;
		parent::__construct($it1);
		$this->it2 = $it2;
	}
	public function rewind() {
		parent::rewind();
		$this->it2->rewind();
	}
	
	public function next() {
		parent::next();
		$this->it2->next();
	}
	
	public function valid() {
		return parent::valid() && $this->it2->valid();
	}
	
	public function current() {
		$it1 = parent::current();
		$it2 = $this->it2->current();
		//print_r($its);
		//var_dump($this->resultfunc);
		return call_user_func_array($this->resultfunc,array($it1,$it2));
	}
	
}