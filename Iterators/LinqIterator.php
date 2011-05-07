<?php
namespace LINQ4PHP\Iterators;

class LinqIterator implements \IteratorAggregate {

    /**
     * @var \Closure Function to return an iterator for this query.
     */
    private $getIteratorFunction;

    /**
     * @static
     * @param  $traversable An Array or Iterator or IteratorAggregate
     * @return ArrayIteratorAggregate|NaiveIteratorAggregate
     */
    public static function asIteratorAggregate(&$traversable) {
        if ($traversable instanceof \IteratorAggregate) {
            return $traversable;
        } elseif (is_array($traversable)) {
            return new ArrayIteratorAggregate($traversable);
        } elseif ($traversable instanceof \Iterator) {
            return new NaiveIteratorAggregate($traversable);
        }
    }


    public function getIterator()
    {
        $itfunc = $this->getIteratorFunction;
        return $itfunc();
    }

    public function __construct(&$iterator)	{
        $from = self::asIteratorAggregate($iterator);
        $this->getIteratorFunction = function () use ($from) { return $from->getIterator();};
	}
		
	public function PrintAll() {
		foreach ($this as $key=>$val) {
			print "key:$key\nVal:".print_r($val,true)."\n";
		}
	}
	
	
	public function SelectMany($collectionSelector, $resultSelector = NULL) {
		
		$currentiterfunc = $this->getIteratorFunction;
		$this->getIteratorFunction = function() use ($currentiterfunc,$collectionSelector,$resultSelector) { return new \RecursiveIteratorIterator(new SelectManyIterator($currentiterfunc(), $collectionSelector,$resultSelector)); };
        return $this;
	}
	
	public function Where($wherefunc) {
        $currentiterfunc = $this->getIteratorFunction;
        $this->getIteratorFunction = function() use ($currentiterfunc,$wherefunc) { return new WhereIterator($currentiterfunc(), $wherefunc); };
        return $this;
	}
	
	public function Select($selector) {
        $currentiterfunc = $this->getIteratorFunction;
		$this->getIteratorFunction = function() use ($currentiterfunc,$selector) { return new TransformIterator($currentiterfunc(), $selector); };
        return $this;
	}
	
	public static function Range($start, $count) {
        return new LinqIterator(new RangeIterator($start, $count));
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
			$i = new WhereIterator($this->getIterator(), $wherefunc);
		} else {
			$i = $this->getIterator();
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
        $currentiterfunc = $this->getIteratorFunction;
		$this->getIteratorFunction = function() use ($currentiterfunc,$list) {
                $concatarr = array($currentiterfunc(),$list);
        		$newlist = new LinqIterator($concatarr);
		        return $newlist->SelectMany(function($i){return $i;});
               };
        return $this;
	}
	
	public function Aggregate($aggfunc) {
		$i = $this->getIterator();
        $i->rewind();
		$ag = $i->current();
		$i->next();
		while($i->valid()) {
			$ag = call_user_func_array($aggfunc,array($ag,$i->current()));
			$i->next();
		}
		return $ag;
	}
	
	public function AggregateWithSeed($seed,$aggfunc,$resfunc = NULL) {
		$i = $this->getIterator();
        $i->rewind();
		$ag = $seed;
		while($i->valid()) {
			$ag = call_user_func_array($aggfunc,array($ag,$i->current()));
			$i->next();
		}
		if (!$resfunc) {
			return $ag;
		} else {
			return call_user_func_array($resfunc,array($ag));
		}
	}
	
	public function Any($matchfunc = NULL) {
		if (!$matchfunc) {
			$list = $this->getIterator();
		} else {
			$list = new WhereIterator($this->getIterator(), $matchfunc);
		}
		$list->rewind();
		return ($list->valid());
	}
	
	public function All($matchfunc) {
		$list = $this->getIterator();
        $list->rewind();
		$i = 0;
		while ($list->valid()) {
			if (!call_user_func_array($matchfunc,array($list->current(),$i++))) {
				return false;
			}	
			$list->next();
		}
		return true;
	}
	
	public function First() {
		$i = $this->getIterator();
        $i->rewind();
		if ($i->valid()) {
			return $i->current();
		} else {
			throw new Exception('First called on Empty List');
		}
	}
	
	public function Last() {
		$i = $this->getIterator();
        $i->rewind();

		if (!$i->valid()) {
			throw new Exception('Last called on Empty List');
		}
		while ($i->valid()) {
			$lastval = $i->current();
			$i->next();
		}
		return $lastval;
	}
	
	public function Single() {
		$i = $this->getIterator();
        $i->rewind();
		if (!$i->valid()) {
			throw new Exception('Single called on Empty List');
		}
		$val =$i->current();
		$i->next();
		if ($i->valid()) {
			throw new Exception('Single called on a List with more than one item');
		}
		return $val;
	}
   
	public function Distinct($comparefunc = NULL) {
		$currentiterfunc = $this->getIteratorFunction;
        $this->getIteratorFunction =
            function() use ($currentiterfunc,$comparefunc) {
                return new DistinctIterator($currentiterfunc(),$comparefunc);
            };
        return $this;

	}

	public function Union($list,$comparefunc = NULL) {
         return $this->Concat($list)->Distinct($comparefunc);
	}
	
	public function Intersect($list,$comparefunc = NULL) {
        $currentiterfunc = $this->getIteratorFunction;
        $this->getIteratorFunction =
            function() use ($currentiterfunc,$list,$comparefunc) {
        		return new IntersectIterator($currentiterfunc(), $list,$comparefunc);
            };
        return $this;
	}
	
	public function Except($list,$comparefunc = NULL) {
        $currentiterfunc = $this->getIteratorFunction;
        $this->getIteratorFunction =
            function() use ($currentiterfunc,$list,$comparefunc) {
                return new ExceptIterator($currentiterfunc(), $list, $comparefunc);
            };
        return $this;
	}
	//TODO: got to here!
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
