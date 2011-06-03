<?php

namespace LINQ4PHP\Iterators;

use LINQ4PHP\LINQ;

class JoinIterator extends DelayedExecutionIterator {

	
	private $keyselectouter;
	private $keyselectjoin;
	private $resultselect;
	private $jointo;
	private $aiterator;
	private $ismatch;
	
	function __construct($iterator,$jointo, $keyselectouter,$keyselectjoin,$resultselect, $comparefunc = NULL) {
		if ($jointo instanceof LinqIterator) {
			$this->jointo = $jointo;
		} else {
			$this->jointo = LINQ::From($jointo);
		}
		
		$this->keyselectouter = $keyselectouter;
		$this->keyselectjoin = $keyselectjoin;
		$this->resultselect = $resultselect;
		$this->ismatch = $comparefunc;
		$this->aiterator = $iterator;
		//we will add our real iterator when we are ready (first access)
		//parent::__construct(new EmptyIterator());	
	}

	protected function firstrun() {
		$lookup = $this->jointo->ToLookup($this->keyselectjoin,NULL,$this->ismatch);
		$outerkeyselector = $this->keyselectouter;
		$resultselector = $this->resultselect;
        $iter = new \RecursiveIteratorIterator(new SelectManyIterator($this->aiterator,
                            function($o) use ($lookup,$outerkeyselector) { return $lookup[call_user_func_array($outerkeyselector,array($o))];},
                            $resultselector));
        //append our iterator to the previous empty one
        //$this->iterator = $iter;
        parent::__construct($iter);
	}
	
}