<?php
namespace LINQ4PHP\Iterators;

class SkipWhileIterator extends IndexedLinqIterator {
	protected $predicate;

	function __construct($iterator, $predicate) {
		$this->predicate = $predicate;
		parent::__construct($iterator);
	}
	
	protected function movetonextmatch() {
		while ($this->valid() && call_user_func_array($this->predicate, array($this->current(),$this->index))) {
			parent::next();	
		}
	}
	
	public function rewind() {
		parent::rewind();
		//need to loop until first match
		$this->movetonextmatch();
	}
	
	
}