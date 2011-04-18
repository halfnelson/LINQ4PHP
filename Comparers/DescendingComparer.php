<?php

namespace LINQ4PHP\Comparers;

class DescendingComparer extends BaseComparer {
	public function Compare($x,$y) {
		//call the parent comparer but swap order of elements to turn into a descending comparer.
		return parent::Compare($y, $x);
	}
}
