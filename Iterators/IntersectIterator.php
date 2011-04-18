<?php
namespace LINQ4PHP\Iterators;
class IntersectIterator extends DelayedExecutionIterator {
	public $ismatch;
	private $secondlist;
	private $secondlistcache = array();
	private $lastkey = NULL;
	
	function __construct($iterator, $intersectwith, $comparefunc = NULL) {
		parent::__construct($iterator);
		if (!$intersectwith instanceof LinqIterator) {
			$this->secondlist = new LinqIterator($intersectwith);
		} else {
			$this->secondlist = $intersectwith;
		}
		$this->ismatch = $comparefunc;
	}
	
	protected function firstrun() {
		foreach ($this->secondlist->Distinct() as $val) {
					$this->secondlistcache[] = $val;
		}
	}
	private function getSecondList() {
		return $this->secondlistcache;
	}
	
	protected function inSecondList() {
		$val = $this->current();
		if ($this->ismatch) {
			foreach ($this->getSecondList() as $key=>$seen) {
				$match = call_user_func_array($this->ismatch,array($seen,$val));
				//short circuit if match is found
				if ($match) {
					$this->lastkey = $key;
					return true;
				}
			}
			//no match found
			return false;
		} else {
			$match = array_search($val,$this->getSecondList(),true);
			if ($match === FALSE) return FALSE;
			$this->lastkey = $match;
			return true;		
		}
	}
	
	protected function movetonextmatch() {
  		while ($this->valid() && !$this->inSecondList()) {
			parent::next();	
  		}
  		//remove current val from secondlist since output needs to be distinct
  		if ($this->valid()) {
  			unset($this->secondlistcache[$this->lastkey]);
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