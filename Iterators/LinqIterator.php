<?php
namespace LINQ4PHP\Iterators;

use LINQ4PHP\LINQ;
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
    public static function getTraversableAsIterator(&$traversable) {
        if ($traversable instanceof \IteratorAggregate) {
            return $traversable->getIterator();
        } elseif ($traversable instanceof \Iterator) {
            return $traversable;
        } elseif (is_array($traversable)) {
            return new \ArrayIterator($traversable);
        }
    }


    public function getIterator()
    {
        $itfunc = $this->getIteratorFunction;
        return $itfunc();
    }

    public function __construct($iteratorfunc)	{
        
        $this->getIteratorFunction = $iteratorfunc;
	}
		
	public function PrintAll() {
		foreach ($this as $key=>$val) {
			print "key:$key\nVal:".print_r($val,true)."\n";
		}
	}
	
	
	public function SelectMany($collectionSelector, $resultSelector = NULL) {
		$currentiterfunc = $this->getIteratorFunction;
		$newiterfunc = function() use ($currentiterfunc,$collectionSelector,$resultSelector) { return new \RecursiveIteratorIterator(new SelectManyIterator($currentiterfunc(), $collectionSelector,$resultSelector)); };
        return new LinqIterator($newiterfunc);
	}
	
	public function Where($wherefunc) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc = function() use ($currentiterfunc,$wherefunc) { return new WhereIterator($currentiterfunc(), $wherefunc); };
        return new LinqIterator($newiterfunc);
	}
	
	public function Select($selector) {
        $currentiterfunc = $this->getIteratorFunction;
		$newiterfunc = function() use ($currentiterfunc,$selector) { return new TransformIterator($currentiterfunc(), $selector); };
        return new LinqIterator($newiterfunc);
	}
	
	public static function Range($start, $count) {
        return LINQ::Linq(new RangeIterator($start, $count));
	}
	/*
	 * in MS Linq this is Empty, Empty is reserved in PHP
	 */
	public static function EmptyList() {
		return LINQ::Linq(new \EmptyIterator());
	}
	public static function Repeat($value,$count) {
		return LINQ::Linq(new RepeatedValueIterator($value, $count));
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
		$newiterfunc = function() use ($currentiterfunc,$list) {
                $concatarr = array($currentiterfunc(),$list);
        		$newlist = LINQ::Linq($concatarr);
		        return $newlist->SelectMany(function($i){return $i;});
               };
        return new LinqIterator($newiterfunc);
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
        $newiterfunc =
            function() use ($currentiterfunc,$comparefunc) {
                return new DistinctIterator($currentiterfunc(),$comparefunc);
            };
        return new LinqIterator($newiterfunc);

	}

	public function Union($list,$comparefunc = NULL) {
         return $this->Concat($list)->Distinct($comparefunc);
	}
	
	public function Intersect($list,$comparefunc = NULL) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$list,$comparefunc) {
        		return new IntersectIterator($currentiterfunc(), $list,$comparefunc);
            };
        return new LinqIterator($newiterfunc);
	}
	
	public function Except($list,$comparefunc = NULL) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$list,$comparefunc) {
                return new ExceptIterator($currentiterfunc(), $list, $comparefunc);
            };
        return new LinqIterator($newiterfunc);
	}

	public function ToLookup($keyselect, $elementselect = NULL, $comparer = NULL) {
	    return new LookupIterator($this->getIterator(), $keyselect,$elementselect, $comparer);
	}
	
	public function Join($list, $keyselectouter, $keyselectinner,$resultselect,$comparer = NULL) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$list,$keyselectouter,$keyselectinner,$resultselect,$comparer) {
	    		return new JoinIterator($currentiterfunc(),$list, $keyselectouter, $keyselectinner, $resultselect,$comparer);
            };
        return new LinqIterator($newiterfunc);
                
	}
	
	public function GroupBy($keyselect,$elementselect=null , $resultselect = null,$comparer = null) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$keyselect,$elementselect,$resultselect,$comparer) {
        		return new GroupByIterator($currentiterfunc(), $keyselect,$elementselect,$resultselect,$comparer);
            };
        return new LinqIterator($newiterfunc);

	}
	
	public function GroupJoin($jointo,$keyselectouter,$keyselectinner,$resultselect,$comparer = null) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$jointo,$keyselectouter,$keyselectinner,$resultselect,$comparer) {
                return new GroupJoinIterator($currentiterfunc(), $jointo, $keyselectouter, $keyselectinner, $resultselect,$comparer);
            };
        return new LinqIterator($newiterfunc);

	}
	
	public function TakeWhile($predicate) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$predicate) {
    			return new TakeWhileIterator($currentiterfunc(), $predicate);
            };
        return new LinqIterator($newiterfunc);
	}
	
	public function Take($count) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$count) {
        		return new TakeWhileIterator($currentiterfunc(), function($val,$idx) use ($count) { return $idx < $count; });
            };
        return new LinqIterator($newiterfunc);

	}
	
	public function SkipWhile($predicate) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$predicate) {
                return new SkipWhileIterator($currentiterfunc(), $predicate);
            };
        return new LinqIterator($newiterfunc);

	}
	
	public function Skip($count) {
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$count) {
    			return new SkipWhileIterator($currentiterfunc(), function($val,$idx) use ($count) { return $idx < $count;} );
            };
        return new LinqIterator($newiterfunc);

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
        $currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc) {
	        	return new ReverseIterator($currentiterfunc());
            };
        return new LinqIterator($newiterfunc);
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
	
	private function GetElementAt($index) {
		$it = $this->getIterator();
        $it->rewind();
		for ($i = 1; $i <= $index; $i++ ) {
			if (!$it->valid()) {
				return FALSE;
			}
			$it->next();
		}	
		if (!$it->valid()) {
			return FALSE;
		} else {
			return array($it->current());
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
	  $it2 = self::getTraversableAsIterator($iterator);
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
		$currentiterfunc = $this->getIteratorFunction;
        $newiterfunc =
            function() use ($currentiterfunc,$list,$resultfunc) {
		        return new ZipIterator($currentiterfunc(), $list, $resultfunc);
            };
        return new LinqIterator($newiterfunc);
	}
	
}
