<?php
namespace LINQ4PHP\Iterators;

use LINQ4PHP\LINQ;
class GroupJoinIterator extends DelayedExecutionIterator {
	private $lookup;
	
	private $keyselectouter;
	private $keyselectjoin;
	private $resultselect;
	private $jointo;

	private $ismatch;
	
	function __construct($iterator,$jointo, $keyselectouter,$keyselectjoin,$resultselect, $comparefunc = NULL) {
		if ($jointo instanceof LinqIterator) {
			$this->jointo = $jointo;
		} else {
			$this->jointo = LINQ::From($jointo);
		}
		$this->keyselectouter = $keyselectouter;
		$this->keyselectjoin = $keyselectjoin;
		$this->resultselect = $resultselect;
		$this->ismatch = $comparefunc;
		parent::__construct($iterator);	
	}
	
	protected function firstrun() {
		$this->lookup = $this->jointo->ToLookup($this->keyselectjoin,NULL,$this->ismatch);
	}
	
	public function current() {
		$val = parent::current();
		$outerkey= call_user_func_array($this->keyselectouter,array($val));
		return call_user_func_array($this->resultselect, array($val,$this->lookup[$outerkey])); 
	}
}
