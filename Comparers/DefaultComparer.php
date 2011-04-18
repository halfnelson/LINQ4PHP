<?php
namespace LINQ4PHP\Comparers;

class DefaultComparer implements IComparer {
	public function Compare($x,$y) {
			if ($x == $y) {
				return 0;
			}
			if ($x < $y) {
				return -1;
			} else {
				return 1;
			}
	}
}