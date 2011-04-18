<?php

namespace LINQ4PHP\Iterators;
class TakeWhileIterator extends IndexedLinqIterator {
	private $predicate;
	
	function __construct($iterator, $predicate) {
		$this->predicate = $predicate;
		parent::__construct($iterator);
	}

	function valid() {
		if (!parent::valid()) { return false; }
		return call_user_func_array($this->predicate,array(parent::current(), $this->index));
	}
	
}