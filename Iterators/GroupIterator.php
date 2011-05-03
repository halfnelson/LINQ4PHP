<?php
namespace LINQ4PHP\Iterators;





//associates a key with an iterator
class GroupIterator extends DelayedExecutionIterator {
	private $elements;
	public $groupkey;
	
	public function __construct($key,$elements) {
		$this->groupkey = $key;
		$this->elements = $elements;
    }

    public function firstrun() {
        parent::__construct(new \ArrayIterator($this->elements));
    }

	public function addElement($element) {
		$this->elements[] = $element;
	}
}
