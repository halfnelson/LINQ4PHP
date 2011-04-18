<?php
namespace LINQ4PHP\Iterators;
class WhereIterator extends SkipWhileIterator {
	
	function __construct($iterator, $wherefunc) {
		parent::__construct($iterator, $wherefunc);
	}
	
    protected function movetonextmatch() {
		while ($this->valid() && !call_user_func_array($this->predicate, array($this->current(),$this->index))) {
			parent::next();	
		}
	}
	
	public function next() {
		parent::next();
		$this->movetonextmatch();
	}
}