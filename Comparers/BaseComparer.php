<?php

namespace LINQ4PHP\Comparers;

class BaseComparer implements IComparer {
	private $comparer;
	public function __construct($comparefunc = null) {
		//accept an Icomparer, a callback or null
		$this->comparer = self::AsIComparer($comparefunc);
	}
	public function Compare($x,$y) {
		return $this->comparer->Compare($x, $y);
	}
	public static function AsIComparer($comparerOrFunc = null) {
		if ($comparerOrFunc instanceof IComparer) {
			return $comparerOrFunc;
		} else {
			//wrap func or null with comparer
			if (is_null($comparerOrFunc)) {
				return new DefaultComparer();
			} elseif (is_callable($comparerOrFunc)) {
				return new CallbackComparer($comparerOrFunc);
			} else {
				//um not possible so complain.
				throw new \Exception("AsIComparer was not given null, or function or Icomparer instance");
			}
		}
	}
}
