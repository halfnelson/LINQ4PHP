<?php
namespace LINQ4PHP\Iterators;
class ExceptIterator extends DistinctIterator {
	private $secondlist;
	private $secondlistread = FALSE;
	
	function __construct($iterator, $list, $comparefunc=NULL) {
		if (!$list instanceof LinqIterator) {
			$this->secondlist = new LinqIterator($list);
		} else {
			$this->secondlist = $list;
		}
		parent::__construct($iterator,$comparefunc);
	}
	
	protected function seenBefore() {
		//load our second list into the seen array.
		if (!$this->secondlistread) {
			foreach ($this->secondlist->Distinct() as $val) {
					$this->seen[] = $val;
			}
			$this->secondlistread = TRUE;
		}
		return parent::seenBefore();
	}
	
}
