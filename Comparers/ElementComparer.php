<?php

namespace LINQ4PHP\Comparers;


class ElementComparer extends BaseComparer {
	
	private $keyselector;
	
	public function __construct($keyselector,$comparer = null) {
		$this->keyselector = $keyselector;
		parent::__construct($comparer);		
	}

	public function Compare($x,$y) {
		$xkey = call_user_func_array($this->keyselector,array($x));
		$ykey = call_user_func_array($this->keyselector,array($y));
		return parent::Compare($xkey, $ykey);
	}
}