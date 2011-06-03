<?php
namespace LINQ4PHP\Iterators;



class LookupIterator extends \IteratorIterator implements \ArrayAccess {
	private $getkey;
	private $getelement;
	private $lookup;
	private $ismatch;

	private function findkey($key) {
		if (!$this->ismatch) {
			if (array_key_exists($key,$this->lookup)) {
				return array(true,$key);
			} else {
				return array(false,$key);
			}
		} else {
			//custom comparer. search the old fashioned way (eww)
			foreach ($this->lookup as $lk=>$lv) {
				if (call_user_func_array($this->ismatch,array($key,$lk))) {
					return array(true,$lk);
				}
			}
            return array(false,$key);
		}
	}
	
	
	
	private function buildlookup($iterator) {
		$this->lookup = array();
		foreach ($iterator as $val) {
			$key = call_user_func_array($this->getkey,array($val));
			if ($this->getelement) {
				$element = call_user_func_array($this->getelement,array($val));
			} else {
				$element = $val;
			}
			list($keyfound,$ak) = $this->findkey($key);
			if (!$keyfound ) {
				$this->lookup[$ak] = new GroupIterator($ak, array());
			} 
			$this->lookup[$ak]->addElement($element);
			
		}
	}
	
	public function __construct($iterator, $keyselect, $elementselect = NULL, $comparer = NULL) {
		$this->getkey = $keyselect;
		$this->getelement = $elementselect;
		$this->ismatch = $comparer;
		
		$this->buildlookup($iterator);
		//build our lookup array and pass to our parent.		
		parent::__construct(new \ArrayIterator($this->lookup));
	}

	public function Count($wherefunc = NULL) {
		if (!$wherefunc) {
            return count($this->lookup);
        } else {
            return parent::Count($wherefunc);
        }
	} 
    public function ContainsKey($key) {
    	return $this->offsetExists($key);
    }
    
	
    // Readonly ArrayAccess []
	public function offsetExists ( $offset ) { list($found,$key) = $this->findkey($offset); return $found; }
    
	public function offsetGet (  $offset ) { 
		list($found,$key) = $this->findkey($offset);
		if (!$found) {
			return new \EmptyIterator();
		}
		return $this->lookup[$key]; 
	}
    
	public function offsetSet (  $offset ,$value ) { throw new \Exception("Cannot modify a LookupIterator via array");}
    public function offsetUnset ( $offset ) { throw new \Exception("Cannot modify a LookupIterator via array");}
	
	
}
