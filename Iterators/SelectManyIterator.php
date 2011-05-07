<?php

namespace LINQ4PHP\Iterators;
/*
 * Extends transform iterator but ensures that the result of each current call is a Recursiveiterator and provides
 * methods to traverse them using recursiveiteratoriterator
 */
class SelectManyIterator extends TransformIterator  implements \RecursiveIterator {
	private $resultselector;
	function __construct($iterator, $transformfunc, $resultfunc = null) {
		parent::__construct($iterator,$transformfunc);
		$this->resultselector = $resultfunc;
	}
	
	public function current() {
		$val = parent::current();
		//if it is an array, wrap it in an iterator  for convienience
		if (is_array($val)) {
			return new SelectManyLeafIterator(new Iterator_Array($val),$this->currentRaw(),$this->resultselector);
		}
		if (!($val instanceof \Iterator) && !($val instanceof \IteratorAggregate)) {
			throw new \Exception('Select Many transform function does not return iterator or array:'.get_class($val));
		} else {
			return new SelectManyLeafIterator($val,$this->currentRaw(),$this->resultselector);
		}
	}
	public function getChildren () {
		return $this->current();
	}
	public function hasChildren () {
		return true;
	}
	
}