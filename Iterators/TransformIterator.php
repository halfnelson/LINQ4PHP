<?php
namespace LINQ4PHP\Iterators;

class TransformIterator extends \IteratorIterator {
	private $atransform;
    function __construct($iterator, $transformfunc) {
		parent::__construct($iterator);
		$this->atransform = $transformfunc;
	}
	public function current() {
		return call_user_func_array($this->atransform, array(parent::current(),$this->key()));
	}
	
	public function currentRaw() {
		return parent::current();
	}
}