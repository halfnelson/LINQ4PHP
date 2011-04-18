<?php
namespace LINQ4PHP\Iterators;

class DistinctIterator extends LinqIterator {
	public $ismatch;
	protected $seen = array();
	function __construct($iterator, $comparefunc= NULL) {
		parent::__construct($iterator);
		$this->ismatch = $comparefunc;
	}
	protected function seenBefore() {
		$val = $this->current();
		if ($this->ismatch) {
			foreach ($this->seen as $seen) {
				$match = call_user_func_array($this->ismatch,array($seen,$val));
				//short circuit if match is found
				if ($match) return true;
			}
			//no match found
			return false;
		} else {
			return in_array($this->current(),$this->seen,true);		
		}
	}
	
	
	protected function movetonextmatch() {
  		while ($this->valid() && $this->seenBefore()) {
			parent::next();	
  		}
  		if ($this->valid()) {
  			$this->seen[] = $this->current();
  		}
	}
	
	public function rewind() {
		parent::rewind();
		//need to loop until first match
		$this->movetonextmatch();
	}
	
	public function next() {
		parent::next();
		$this->movetonextmatch();
	}
}