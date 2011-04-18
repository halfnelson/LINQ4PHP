<?php
namespace LINQ4PHP\Iterators;
class RepeatedValueIterator extends RangeIterator {
	private $val;
	public function __construct($value, $count) {
		$this->val = $value;
		parent::__construct(0,$count);
	}
	//return val instead of index
	function current() {
		return $this->val;
	}
}