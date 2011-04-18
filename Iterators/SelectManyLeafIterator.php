<?php

namespace LINQ4PHP\Iterators;
class SelectManyLeafIterator extends LinqIterator implements \RecursiveIterator {
	private $resultselector;
	private $aparent;
	function __construct($iterator, $aparent, $resultfunc = NULL) {
		parent::__construct($iterator);
		$this->aparent = $aparent;
		$this->resultselector = $resultfunc;
	}
	
	public function current() {
		$val = parent::current();
		if (!$this->resultselector) {
			return $val;
		} else {
			//project result
			return call_user_func_array($this->resultselector,array($this->aparent,$val));
		}
	}
	
	public function getChildren () {
		throw new Exception('GET CHILDREN CANNOT BE CALLED ON A LEAF');
	}
	public function hasChildren ( ) {
		return false;
	}
}