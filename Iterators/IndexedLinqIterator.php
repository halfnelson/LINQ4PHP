<?php
namespace LINQ4PHP\Iterators;
class IndexedLinqIterator extends \IteratorIterator {
	protected $index;
	function __construct($iterator) {
		parent::__construct($iterator);
		$this->index = -1;
	}
	
	public function rewind() {
		$this->index = 0;
		parent::rewind();
	}
	
	public function next() {
		$this->index++;
		parent::next();
	}
	
}