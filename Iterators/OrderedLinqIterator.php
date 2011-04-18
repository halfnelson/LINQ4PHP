<?php
namespace LINQ4PHP\Iterators;

// TODO: Only call keyselector once per item. May get called heaps by usort 

use \LINQ4PHP;

class OrderedLinqIterator extends DelayedExecutionIterator {
	private $currentcomparer;
	private $currentkeyselector;
	private $source;
	
	public function __construct($source, $keyselector, $comparer) {
		$this->source = $source;
		$this->currentcomparer = $comparer;
		$this->currentkeyselector = $keyselector;
		//call parent::__Construct later.
	}
	
	public static function getComparer($comparer, $descending) {
		$newcomparer = LINQ4PHP\Comparers\BaseComparer::AsIComparer($comparer);
		if ($descending) {
			$newcomparer = new LINQ4PHP\Comparers\DescendingComparer($newcomparer);
		}
		return $newcomparer;
	}
	
	public function firstrun() {
		
		//construct sorted iterator
		//data as array
		$data = $this->source->ToArray();
		$datalen = count($data);
		$keys = array();
		//keys precalced as array
		foreach ($data as $v) {
			$keys[] = call_user_func_array($this->currentkeyselector,array($v));
		}
		
		
		//sort keys
		uasort($keys,array($this->currentcomparer,'Compare'));
		
		//map key order onto data order
		$newdata = array();
		foreach ($keys as $k=>$v) {
			$newdata[] = $data[$k];
		}
		parent::__construct(new LinqIterator($newdata));
	}
	
	private function CreateOrderedLinqIterator($keyselector,$comparer,$descending) {
		//create new comparer
		$newcomparer = self::getComparer($comparer, $descending);
		
		//combine the new comparer with the existing one
		$newcomparer = new LINQ4PHP\Comparers\ThenByComparer($this->currentcomparer,$newcomparer);
		
	    //combine the new key with the existing one.
	    $currentkeyselector = $this->currentkeyselector;

	    $newkeyselector = function($element) use ($currentkeyselector,$keyselector) {
	    	return new LINQ4PHP\Comparers\ThenByKey(call_user_func_array($currentkeyselector,array($element)),
	    					     call_user_func_array($keyselector,array($element)));
	    };

	    //return new iterator, ophaning this one.
		return new OrderedLinqIterator($this->source, $newkeyselector, $newcomparer);
	}
	
	public function ThenBy($keyselector, $comparer = null) {
		return $this->CreateOrderedLinqIterator($keyselector,$comparer,false);
	}
	
	public function ThenByDescending($keyselector, $comparer = null) {
		return $this->CreateOrderedLinqIterator($keyselector,$comparer,true);
	}
	
}
