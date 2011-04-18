<?php
namespace LINQ4PHP\Iterators;

class LinqIterator extends \IteratorIterator {

	public function __construct($iterator)	{
		//if iterator is an array, wrap it here
		if (is_array($iterator)) {
			$iterator = new Iterator_Array($iterator);
		}
		parent::__construct($iterator);
	}
		
	public function PrintAll() {
		foreach ($this as $key=>$val) {
			print "key:$key\nVal:".print_r($val,true)."\n";
		}
	}
	
	
	public function SelectMany($collectionSelector, $resultSelector = NULL) {
		
		return new LinqIterator(new \RecursiveIteratorIterator(new SelectManyIterator($this, $collectionSelector,$resultSelector)));
		
	}	
	
	public function Where($wherefunc) {
		return new WhereIterator($this, $wherefunc);
	}
	
	public function Select($selector) {
		return new TransformIterator($this, $selector);
	}
	
	public static function Range($start, $count) {
		return  new LinqIterator(new RangeIterator($start, $count));
	}
	/*
	 * in MS Linq this is Empty, Empty is reserved in PHP
	 */
	public static function EmptyList() {
		return new LinqIterator(new \EmptyIterator());
	}
	public static function Repeat($value,$count) {
		return new LinqIterator(new RepeatedValueIterator($value, $count));
	}
	
	public function Count($wherefunc = NULL) {
		if ($wherefunc) {
			$i = new WhereIterator($this, $wherefunc);
		} else {
			$i = $this;
		}
		$count =0;
		$i->rewind();
		while($i->valid()) {
			$count++;
			$i->next();
		}
		return $count;
		
	} 
	
	public function Concat($list) {
		$newlist = new LinqIterator(array($this,$list));
		return $newlist->SelectMany(function($i){return $i;});
	}
	
	public function Aggregate($aggfunc) {
		$this->rewind();
		$ag = $this->current();
		$this->next();
		while($this->valid()) {
			$ag = call_user_func_array($aggfunc,array($ag,$this->current()));
			$this->next();
		}
		return $ag;
	}
	
	public function AggregateWithSeed($seed,$aggfunc,$resfunc = NULL) {
		$this->rewind();
		$ag = $seed;
		while($this->valid()) {
			$ag = call_user_func_array($aggfunc,array($ag,$this->current()));
			$this->next();
		}
		if (!$resfunc) {
			return $ag;
		} else {
			return call_user_func_array($resfunc,array($ag));
		}
	}
	
	public function Any($matchfunc = NULL) {
		if (!$matchfunc) {
			$list = $this;
		} else {
			$list = new WhereIterator($this, $matchfunc);
		}
		$list->rewind();
		return ($this->valid());
	}
	
	public function All($matchfunc) {
		$this->rewind();
		$i = 0;
		while ($this->valid()) {
			if (!call_user_func_array($matchfunc,array($this->current(),$i++))) {
				return false;
			}	
			$this->next();
		}
		return true;
	}
	
	public function First() {
		$this->rewind();
		if ($this->valid()) {
			return $this->current();
		} else {
			throw new Exception('First called on Empty List');
		}
	}
	
	public function Last() {
		$this->rewind();
		if (!$this->valid()) {
			throw new Exception('Last called on Empty List');
		}
		while ($this->valid()) {
			$lastval = $this->current();
			$this->next(); 
		}
		return $lastval;
	}
	
	public function Single() {
		$this->rewind();
		if (!$this->valid()) {
			throw new Exception('Single called on Empty List');
		}
		$val =$this->current();
		$this->next();
		if ($this->valid()) {
			throw new Exception('Single called on a List with more than one item');
		}
		return $val;
	}
   
	public function Distinct($comparefunc = NULL) {
		return new DistinctIterator($this,$comparefunc);
	}

	public function Union($list,$comparefunc = NULL) {
		return $this->Concat($list)->Distinct($comparefunc);
	}
	
	public function Intersect($list,$comparefunc = NULL) {
		return new IntersectIterator($this, $list,$comparefunc);
	}
	
	public function Except($list,$comparefunc = NULL) {
		return new ExceptIterator($this, $list, $comparefunc);
	}
	
	public function ToLookup($keyselect, $elementselect = NULL, $comparer = NULL) {
		return new LookupIterator($this, $keyselect,$elementselect, $comparer);
	}
	
	public function Join($list, $keyselectouter, $keyselectinner,$resultselect,$comparer = NULL) {
		return new JoinIterator($this,$list, $keyselectouter, $keyselectinner, $resultselect,$comparer);	
	}
	
	public function GroupBy($keyselect,$elementselect=null , $resultselect = null,$comparer = null) {
		return new GroupByIterator($this, $keyselect,$elementselect,$resultselect,$comparer);
	}
	
	public function GroupJoin($jointo,$keyselectouter,$keyselectinner,$resultselect,$comparer = null) {
		return new GroupJoinIterator($this, $jointo, $keyselectouter, $keyselectinner, $resultselect,$comparer);
	}
	
	public function TakeWhile($predicate) {
		return new TakeWhileIterator($this, $predicate);
	}
	
	public function Take($count) {
		return new TakeWhileIterator($this, function($val,$idx) use ($count) { return $idx < $count; });
	}
	
	public function SkipWhile($predicate) {
		return new SkipWhileIterator($this, $predicate);
	}
	
	public function Skip($count) {
		return new SkipWhileIterator($this, function($val,$idx) use ($count) { return $idx < $count;} );
	}
	
	public function ToArray() {
		$a= array();
		foreach ($this as $v) {
			$a[] = $v;
		}
		return $a;
	}
	
	public function ToList() {
		$l = new \SplDoublyLinkedList();
		foreach ($this as $v) {
			$l->push($v);
		} 
		return $l;
	}
	
	public function OrderBy($keyselector, $comparer = null) {
		return new OrderedLinqIterator($this, $keyselector, OrderedLinqIterator::getComparer($comparer,false));
	}
	
	public function OrderByDescending($keyselector, $comparer = null) {
		return new OrderedLinqIterator($this, $keyselector, OrderedLinqIterator::getComparer($comparer,true));
	}
	
	public function Reverse() {
		return new ReverseIterator($this);
	}
	
	public function Sum($elementselect = null) {
		if ($elementselect) {
			return $this->Select($elementselect)->Sum();
		} else {
			$sum = 0;
			foreach ($this as $v) {
				$sum = $sum + $v;
			}
			return $sum;
		}
	}
	
	public function Max($elementselect = null) {
		if ($elementselect) {
			return $this->Select($elementselect)->Max();
		} else {
			$max = NULL;
			foreach ($this as $v) {
				if ($v > $max) $max = $v;
			}
			return $max;
		}
	}
	
	public function Min($elementselect = null) {
		if ($elementselect) {
			return $this->Select($elementselect)->Min();
		} else {
			$min = NULL;
			$hasmin = false;
			foreach ($this as $v) {
				if (!$hasmin) {
					$min = $v;
					$hasmin = true;
				}
				if ($v < $min) $min = $v;
			}
			return $min;
		}
	}
	
	public function Average($elementselect = null) {
		if ($elementselect) {
			return $this->Select($elementselect)->Average();
		} else {
			$sum = NULL;
			$count = 0;
			foreach ($this as $v) {
				if (!is_null($v)) {
					$count++;
					$sum += $v;
				}
			}
			if ($count == 0) {
				return NULL;
			} else {
				return $sum/$count;
			}
		}
	}
	
	//TODO: Optimise for this implementing array access
	private function GetElementAt($index) {
		$this->rewind();
		for ($i = 1; $i <= $index; $i++ ) {
			if (!$this->valid()) {
				return FALSE;
			}
			$this->next();
		}	
		if (!$this->valid()) {
			return FALSE;
		} else {
			return array($this->current());
		}
	}
	
	public function ElementAt($index) {
		$fetchval = $this->GetElementAt($index);
		if ($fetchval == FALSE) {
			throw new \Exception("Index out of range");
		} else {
			return $fetchval[0];
		}
	}
	
	public function ElementAtOrDefault($index) {
		$fetchval = $this->GetElementAt($index);
		if ($fetchval == FALSE) {
			return NULL;
		} else {
			return $fetchval[0];
		}
	}
	
	public function Contains($value,$comparer = null) {
		$contains = FALSE;
		foreach ($this as $v) {
			if ($comparer) {
				$contains = call_user_func_array($comparer,array($v,$value));
			} else {
				$contains = ($v === $value);
			}
			if ($contains) return TRUE;
		}
		return $contains;
	}
	public function SequenceEqual($iterator, $comparer = null) {
	  if(is_array($iterator)) {
	  	$it2= new \ArrayIterator($iterator);
	  } else {
	  	$it2 = $iterator;
	  }
      $it2->rewind();
	  foreach ($this as $it) {	
	  		if ($comparer) {
				$eq = call_user_func_array($comparer,array($it,$it2->current()));
			} else {
				$eq = ($it === $it2->current());
			} 
	  		if (!$eq) return FALSE;
		    $it2->next();
	  }
	  //list 2 was longer!
	  if ($it2->valid()) {
	  	  return FALSE;
	  }
	  return TRUE;
	}
	
	public function Zip($list,$resultfunc) {
		
		return new LinqIterator(new ZipIterator($this, $list, $resultfunc));
	}
	
}
