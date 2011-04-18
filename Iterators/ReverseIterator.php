<?php
namespace LINQ4PHP\Iterators;

class ReverseIterator extends DelayedExecutionIterator {
	private $source;
	public function __construct($source) {
		$this->source = $source;
	}
	protected function firstrun() {
		$data = new \SplStack();
		foreach ($this->source as $val) {
			$data->push($val);
		}
		parent::__construct($data);
	}
	
}