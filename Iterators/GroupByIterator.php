<?php
namespace LINQ4PHP\Iterators;
class GroupByIterator extends DelayedExecutionIterator {

	
	private $keyselect;
	private $elementselect;
	private $resultselect;
	private $aiterator;
	private $ismatch;
	
	function __construct($iterator,$keyselect,$elementselect = null, $resultselect = null, $comparer = null) {
		$this->keyselect = $keyselect;
		$this->elementselect = $elementselect;
		$this->resultselect = $resultselect;
		$this->aiterator = $iterator;
		$this->ismatch = $comparer;
	}
	
	public function firstrun() {
		$lookup = new LookupIterator($this->aiterator,$this->keyselect,$this->elementselect,$this->ismatch);
		if ($this->resultselect) {
			$resultsel = $this->resultselect;
			$lookup = new TransformIterator($lookup,function(GroupIterator $group) use ($resultsel) {
			   	   return call_user_func_array($resultsel,array($group->groupkey,$group));
			   });
            parent::__construct($lookup);
		} else {
			parent::__construct($lookup);
		}
		
	}
	
	
}